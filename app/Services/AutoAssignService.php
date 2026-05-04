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
     * Otomatis menetapkan lokasi KPM (desa) dan PPL (sekolah) untuk mahasiswa.
     *
     * Kondisi 1: Cari lokasi terdekat dari domisili mahasiswa dalam radius max_radius_km.
     * Kondisi 2: Jika kondisi 1 tidak terpenuhi, cari pasangan desa KPM + sekolah PPL
     *            yang paling berdekatan satu sama lain (dengan kuota tersedia).
     *
     * @return array{KPM: array|null, PPL: array|null}
     */
    public function assign(MahasiswaProfile $profile): array
    {
        $radius  = (float) Setting::get('max_radius_km', 10);
        $results = ['KPM' => null, 'PPL' => null];

        foreach (['KPM', 'PPL'] as $program) {
            if ($this->alreadyRegistered($profile, $program)) {
                continue;
            }

            // Cek gelombang aktif untuk program ini
            $gelombang = Gelombang::activeFor($program);
            if (! $gelombang || ! $gelombang->isOpen()) {
                $results[$program] = ['school' => null, 'method' => null, 'gelombang' => null, 'reason' => 'no_active_wave'];
                continue;
            }

            // Kondisi 1: berdasarkan lokasi domisili mahasiswa
            $school = $this->findNearestWithinRadius($profile, $program, $radius);
            if ($school) {
                $results[$program] = ['school' => $school, 'method' => 'domisili', 'gelombang' => $gelombang];
            } else {
                // Tandai perlu kondisi 2
                $results[$program] = ['school' => null, 'method' => 'pending_proximity', 'gelombang' => $gelombang];
            }
        }

        // Kondisi 2: untuk yang butuh proximity
        $needProximity = array_values(array_filter(
            ['KPM', 'PPL'],
            fn ($p) => isset($results[$p]['method']) && $results[$p]['method'] === 'pending_proximity'
        ));

        if (! empty($needProximity)) {
            $this->assignByProximity($profile, $needProximity, $results);
        }

        // Simpan ke database
        foreach (['KPM', 'PPL'] as $program) {
            $r = $results[$program] ?? null;
            if (! $r || ! $r['school'] || $this->alreadyRegistered($profile, $program)) {
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
     * Kondisi 1: Sekolah terdekat dalam radius dari domisili mahasiswa dengan kuota tersedia.
     */
    private function findNearestWithinRadius(MahasiswaProfile $profile, string $program, float $radius): ?School
    {
        return School::query()
            ->where('is_active', true)
            ->where(function ($q) use ($program) {
                $q->where('program', $program)->orWhere('program', 'BOTH');
            })
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
     * Kondisi 2: Tetapkan berdasarkan kedekatan antar lokasi KPM & PPL.
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

            if ($anchor) {
                $best = $candidates
                    ->map(function (School $s) use ($anchor) {
                        $s->proximity = Geo::distanceKm(
                            (float) $anchor->latitude,
                            (float) $anchor->longitude,
                            (float) $s->latitude,
                            (float) $s->longitude,
                        );

                        return $s;
                    })
                    ->sortBy('proximity')
                    ->first();
            } else {
                $best = $candidates
                    ->map(function (School $s) use ($profile) {
                        $s->proximity = Geo::distanceKm(
                            (float) $profile->latitude,
                            (float) $profile->longitude,
                            (float) $s->latitude,
                            (float) $s->longitude,
                        );

                        return $s;
                    })
                    ->sortBy('proximity')
                    ->first();
            }

            $results[$prog]['school']  = $best;
            $results[$prog]['method']  = 'proximity';

            return;
        }

        // Kedua program belum ditetapkan: cari pasangan (KPM, PPL) yang paling berdekatan
        $kpmCandidates = $this->getAllWithSlots('KPM');
        $pplCandidates = $this->getAllWithSlots('PPL');

        if ($kpmCandidates->isEmpty() && $pplCandidates->isEmpty()) {
            foreach ($unassigned as $p) {
                $results[$p]['method'] = 'proximity';
            }

            return;
        }

        if ($kpmCandidates->isEmpty()) {
            $best = $this->nearestToProfile($profile, $pplCandidates);
            $results['PPL']['school'] = $best;
            $results['PPL']['method'] = 'proximity';

            return;
        }
        if ($pplCandidates->isEmpty()) {
            $best = $this->nearestToProfile($profile, $kpmCandidates);
            $results['KPM']['school'] = $best;
            $results['KPM']['method'] = 'proximity';

            return;
        }

        $bestKpm = null;
        $bestPpl = null;
        $minDist = PHP_FLOAT_MAX;

        foreach ($kpmCandidates as $kpm) {
            foreach ($pplCandidates as $ppl) {
                $dist = Geo::distanceKm(
                    (float) $kpm->latitude,
                    (float) $kpm->longitude,
                    (float) $ppl->latitude,
                    (float) $ppl->longitude,
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
            ->where(function ($q) use ($program) {
                $q->where('program', $program)->orWhere('program', 'BOTH');
            })
            ->get()
            ->filter(fn (School $s) => $s->availableSlots($program) > 0);
    }

    private function nearestToProfile(MahasiswaProfile $profile, Collection $candidates): ?School
    {
        return $candidates
            ->map(function (School $s) use ($profile) {
                $s->proximity = Geo::distanceKm(
                    (float) $profile->latitude,
                    (float) $profile->longitude,
                    (float) $s->latitude,
                    (float) $s->longitude,
                );

                return $s;
            })
            ->sortBy('proximity')
            ->first();
    }
}
