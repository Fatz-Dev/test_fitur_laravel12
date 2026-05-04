<?php

namespace App\Services;

use App\Models\Gelombang;
use App\Models\MahasiswaProfile;
use App\Models\Registration;
use App\Models\School;
use App\Models\Setting;
use App\Support\Geo;
use Illuminate\Support\Collection;

class AutoAssignService
{
    /**
     * Otomatis menetapkan lokasi KPM (desa) dan/atau PPL (sekolah) untuk mahasiswa
     * sesuai pilihan program yang dipilih saat pendaftaran.
     *
     * KPM saja  → Kondisi 1 (radius) lalu Kondisi 2 jika perlu
     * PPL saja  → Kondisi 1 (radius) lalu Kondisi 2 jika perlu
     * PKPPM     → Langsung Kondisi 2: cari pasangan desa KPM + sekolah PPL
     *             yang paling berdekatan satu sama lain
     *
     * @return array{KPM: array|null, PPL: array|null}
     */
    public function assign(MahasiswaProfile $profile): array
    {
        $radius        = (float) Setting::get('max_radius_km', 10);
        $results       = ['KPM' => null, 'PPL' => null];
        $programs      = $profile->programsToAssign();   // ['KPM'], ['PPL'], atau ['KPM','PPL']
        $isPkppm       = $profile->program_choice === 'PKPPM';

        // ── Cek gelombang aktif untuk setiap program yang diperlukan ──────────
        foreach ($programs as $program) {
            if ($this->alreadyRegistered($profile, $program)) {
                continue;
            }

            $gelombang = Gelombang::activeFor($program);
            if (! $gelombang || ! $gelombang->isOpen()) {
                $results[$program] = [
                    'school'    => null,
                    'method'    => null,
                    'gelombang' => null,
                    'reason'    => 'no_active_wave',
                ];
                continue;
            }

            // Tandai sebagai "perlu penetapan"
            $results[$program] = [
                'school'    => null,
                'method'    => 'pending',
                'gelombang' => $gelombang,
            ];
        }

        // ── Penetapan berdasarkan pilihan program ──────────────────────────────
        $toAssign = array_values(array_filter(
            $programs,
            fn ($p) => isset($results[$p]['method']) && $results[$p]['method'] === 'pending'
        ));

        if (empty($toAssign)) {
            // Semua sudah terdaftar atau tidak ada gelombang aktif
            return $this->saveAndReturn($profile, $results);
        }

        if ($isPkppm) {
            // ── PKPPM: langsung Kondisi 2 — cari pasangan desa+sekolah terdekat ──
            foreach ($toAssign as $p) {
                $results[$p]['method'] = 'pending_proximity';
            }
            $this->assignByProximity($profile, $toAssign, $results);
        } else {
            // ── KPM atau PPL saja: Kondisi 1 dulu, lalu Kondisi 2 jika tidak ada ─
            foreach ($toAssign as $program) {
                $school = $this->findNearestWithinRadius($profile, $program, $radius);
                if ($school) {
                    $results[$program]['school'] = $school;
                    $results[$program]['method'] = 'domisili';
                } else {
                    $results[$program]['method'] = 'pending_proximity';
                }
            }

            $needProximity = array_values(array_filter(
                $toAssign,
                fn ($p) => $results[$p]['method'] === 'pending_proximity'
            ));

            if (! empty($needProximity)) {
                $this->assignByProximity($profile, $needProximity, $results);
            }
        }

        return $this->saveAndReturn($profile, $results);
    }

    /**
     * Simpan hasil penetapan ke tabel registrations dan kembalikan $results.
     */
    private function saveAndReturn(MahasiswaProfile $profile, array $results): array
    {
        foreach (['KPM', 'PPL'] as $program) {
            $r = $results[$program] ?? null;
            if (! $r || ! ($r['school'] ?? null) || $this->alreadyRegistered($profile, $program)) {
                continue;
            }
            $school    = $r['school'];
            $gelombang = $r['gelombang'];
            $distance  = Geo::distanceKm(
                (float) $profile->latitude,
                (float) $profile->longitude,
                (float) $school->latitude,
                (float) $school->longitude,
            );
            Registration::create([
                'mahasiswa_profile_id' => $profile->id,
                'school_id'            => $school->id,
                'gelombang_id'         => $gelombang?->id,
                'program'              => $program,
                'distance_km'          => round($distance, 3),
                'status'               => 'pending',
            ]);
        }

        return $results;
    }

    private function alreadyRegistered(MahasiswaProfile $profile, string $program): bool
    {
        return $profile->registrations()
            ->where('program', $program)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    /**
     * Kondisi 1: Lokasi terdekat dalam radius dari domisili mahasiswa dengan kuota tersedia.
     */
    private function findNearestWithinRadius(MahasiswaProfile $profile, string $program, float $radius): ?School
    {
        return School::query()
            ->where('is_active', true)
            ->where('program', $program)
            ->get()
            ->map(function (School $s) use ($profile) {
                $s->distance = Geo::distanceKm(
                    (float) $profile->latitude,
                    (float) $profile->longitude,
                    (float) $s->latitude,
                    (float) $s->longitude,
                );

                return $s;
            })
            ->filter(fn (School $s) => $s->distance <= $radius && $s->availableSlots($program) > 0)
            ->sortBy('distance')
            ->first();
    }

    /**
     * Kondisi 2: Tetapkan berdasarkan kedekatan antar lokasi KPM (desa) & PPL (sekolah).
     * Untuk PKPPM, ini adalah satu-satunya kondisi yang digunakan.
     */
    private function assignByProximity(MahasiswaProfile $profile, array $unassigned, array &$results): void
    {
        if (count($unassigned) === 1) {
            $prog   = $unassigned[0];
            $other  = $prog === 'KPM' ? 'PPL' : 'KPM';
            $anchor = $results[$other]['school'] ?? null;

            $candidates = $this->getAllWithSlots($prog);
            if ($candidates->isEmpty()) {
                $results[$prog]['method'] = 'proximity';

                return;
            }

            $best = $anchor
                ? $candidates->map(function (School $s) use ($anchor) {
                    $s->proximity = Geo::distanceKm(
                        (float) $anchor->latitude, (float) $anchor->longitude,
                        (float) $s->latitude, (float) $s->longitude,
                    );

                    return $s;
                })->sortBy('proximity')->first()
                : $candidates->map(function (School $s) use ($profile) {
                    $s->proximity = Geo::distanceKm(
                        (float) $profile->latitude, (float) $profile->longitude,
                        (float) $s->latitude, (float) $s->longitude,
                    );

                    return $s;
                })->sortBy('proximity')->first();

            $results[$prog]['school'] = $best;
            $results[$prog]['method'] = 'proximity';

            return;
        }

        // Kedua program belum ditetapkan: cari pasangan (desa_kpm × sekolah_ppl) terdekat
        $kpmCandidates = $this->getAllWithSlots('KPM');
        $pplCandidates = $this->getAllWithSlots('PPL');

        if ($kpmCandidates->isEmpty() && $pplCandidates->isEmpty()) {
            foreach ($unassigned as $p) {
                $results[$p]['method'] = 'proximity';
            }

            return;
        }

        if ($kpmCandidates->isEmpty()) {
            $results['PPL']['school'] = $this->nearestToProfile($profile, $pplCandidates);
            $results['PPL']['method'] = 'proximity';

            return;
        }

        if ($pplCandidates->isEmpty()) {
            $results['KPM']['school'] = $this->nearestToProfile($profile, $kpmCandidates);
            $results['KPM']['method'] = 'proximity';

            return;
        }

        // Iterasi semua kombinasi: cari pasangan dengan jarak antar-lokasi terkecil
        $bestKpm = null;
        $bestPpl = null;
        $minDist = PHP_FLOAT_MAX;

        foreach ($kpmCandidates as $kpm) {
            foreach ($pplCandidates as $ppl) {
                $dist = Geo::distanceKm(
                    (float) $kpm->latitude, (float) $kpm->longitude,
                    (float) $ppl->latitude, (float) $ppl->longitude,
                );
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $bestKpm = $kpm;
                    $bestPpl = $ppl;
                }
            }
        }

        if ($bestKpm) {
            $results['KPM']['school'] = $bestKpm;
            $results['KPM']['method'] = 'proximity';
        }
        if ($bestPpl) {
            $results['PPL']['school'] = $bestPpl;
            $results['PPL']['method'] = 'proximity';
        }
    }

    private function getAllWithSlots(string $program): Collection
    {
        return School::query()
            ->where('is_active', true)
            ->where('program', $program)
            ->get()
            ->filter(fn (School $s) => $s->availableSlots($program) > 0);
    }

    private function nearestToProfile(MahasiswaProfile $profile, Collection $candidates): ?School
    {
        return $candidates
            ->map(function (School $s) use ($profile) {
                $s->proximity = Geo::distanceKm(
                    (float) $profile->latitude, (float) $profile->longitude,
                    (float) $s->latitude, (float) $s->longitude,
                );

                return $s;
            })
            ->sortBy('proximity')
            ->first();
    }
}
