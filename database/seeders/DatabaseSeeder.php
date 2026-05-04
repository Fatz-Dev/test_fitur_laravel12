<?php

namespace Database\Seeders;

use App\Models\Gelombang;
use App\Models\MahasiswaProfile;
use App\Models\Registration;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan semua data
        DB::statement('PRAGMA foreign_keys = OFF');
        Registration::query()->delete();
        MahasiswaProfile::query()->delete();
        User::query()->delete();
        School::query()->delete();
        Gelombang::query()->delete();
        DB::table('settings')->delete();
        DB::statement('PRAGMA foreign_keys = ON');

        // ─── Admin ────────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Admin Kampus',
            'email'    => 'admin@kampus.ac.id',
            'password' => 'admin123',
            'role'     => 'admin',
        ]);

        // ─── Pengaturan ───────────────────────────────────────────────────────
        Setting::put('max_radius_km', 10);
        Setting::put('institution_name', 'Kampus Pendidikan');

        // ─── Gelombang ────────────────────────────────────────────────────────
        $gelombangKpm = Gelombang::create([
            'program'        => 'KPM',
            'nomor'          => 1,
            'tahun_akademik' => '2024/2025',
            'tanggal_buka'   => now()->subMonth()->toDateString(),
            'tanggal_tutup'  => now()->addMonths(2)->toDateString(),
            'is_active'      => true,
        ]);

        $gelombangPpl = Gelombang::create([
            'program'        => 'PPL',
            'nomor'          => 1,
            'tahun_akademik' => '2024/2025',
            'tanggal_buka'   => now()->subMonth()->toDateString(),
            'tanggal_tutup'  => now()->addMonths(3)->toDateString(),
            'is_active'      => true,
        ]);

        // ─── Sekolah ──────────────────────────────────────────────────────────
        $schools = [
            ['SDN 01 Jakarta Pusat',    'SD',  'Jl. Merdeka No. 1',        -6.1751, 106.8650, 'BOTH', 5, 5],
            ['SMPN 02 Jakarta Selatan', 'SMP', 'Jl. Kebon Jeruk',          -6.2615, 106.8106, 'BOTH', 4, 4],
            ['SMAN 03 Jakarta Timur',   'SMA', 'Jl. Matraman 88',          -6.2088, 106.8519, 'PPL',  0, 6],
            ['SMKN 4 Jakarta Barat',    'SMK', 'Jl. Daan Mogot 12',        -6.1683, 106.7589, 'PPL',  0, 5],
            ['MI Al-Hidayah',           'MI',  'Jl. Cempaka Putih 5',      -6.1722, 106.8730, 'KPM',  6, 0],
            ['MTs Al-Falah',            'MTs', 'Jl. Tebet Raya 22',        -6.2295, 106.8543, 'BOTH', 3, 3],
            ['MAN 1 Jakarta',           'MA',  'Jl. Asem Baris 9',         -6.2440, 106.8453, 'BOTH', 4, 4],
            ['SDN 05 Bekasi',           'SD',  'Jl. Cut Mutia, Bekasi',    -6.2349, 106.9896, 'BOTH', 5, 5],
        ];

        $createdSchools = [];
        foreach ($schools as [$name, $jenjang, $addr, $lat, $lng, $prog, $kpm, $ppl]) {
            $createdSchools[$name] = School::create([
                'name'       => $name,
                'jenjang'    => $jenjang,
                'address'    => $addr,
                'latitude'   => $lat,
                'longitude'  => $lng,
                'program'    => $prog,
                'kuota_kpm'  => $kpm,
                'kuota_ppl'  => $ppl,
                'is_active'  => true,
            ]);
        }

        // ─── Mahasiswa 1: Profil kosong (tidak ada penempatan) ────────────────
        $u1 = User::create([
            'name'     => 'Andi Kosong',
            'email'    => 'andi.kosong@kampus.ac.id',
            'password' => 'password123',
            'role'     => 'mahasiswa',
        ]);
        MahasiswaProfile::create([
            'user_id'             => $u1->id,
            'nim'                 => '2110001',
            'phone'               => '081111111111',
            'address'             => 'Jl. Merdeka Barat No. 10, Jakarta Pusat',
            'latitude'            => -6.1751,
            'longitude'           => 106.8650,
            'microteaching_grade' => 'A',
            'status'              => 'approved',
            'reviewed_at'         => now(),
        ]);

        // ─── Mahasiswa 2: Sudah ada penempatan KPM ────────────────────────────
        $u2 = User::create([
            'name'     => 'Budi Nur KPM',
            'email'    => 'budi.kpm@kampus.ac.id',
            'password' => 'password123',
            'role'     => 'mahasiswa',
        ]);
        $p2 = MahasiswaProfile::create([
            'user_id'             => $u2->id,
            'nim'                 => '2110002',
            'phone'               => '082222222222',
            'address'             => 'Jl. Cempaka Putih No. 5, Jakarta Pusat',
            'latitude'            => -6.1722,
            'longitude'           => 106.8730,
            'microteaching_grade' => 'B',
            'status'              => 'approved',
            'reviewed_at'         => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p2->id,
            'school_id'            => $createdSchools['MI Al-Hidayah']->id,
            'gelombang_id'         => $gelombangKpm->id,
            'program'              => 'KPM',
            'distance_km'          => 0.24,
            'status'               => 'pending',
        ]);

        // ─── Mahasiswa 3: Sudah ada penempatan PPL ────────────────────────────
        $u3 = User::create([
            'name'     => 'Citra Hanya PPL',
            'email'    => 'citra.ppl@kampus.ac.id',
            'password' => 'password123',
            'role'     => 'mahasiswa',
        ]);
        $p3 = MahasiswaProfile::create([
            'user_id'             => $u3->id,
            'nim'                 => '2110003',
            'phone'               => '083333333333',
            'address'             => 'Jl. Matraman Raya No. 10, Jakarta Timur',
            'latitude'            => -6.2088,
            'longitude'           => 106.8519,
            'microteaching_grade' => 'A',
            'status'              => 'approved',
            'reviewed_at'         => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p3->id,
            'school_id'            => $createdSchools['SMAN 03 Jakarta Timur']->id,
            'gelombang_id'         => $gelombangPpl->id,
            'program'              => 'PPL',
            'distance_km'          => 0.00,
            'status'               => 'pending',
        ]);

        // ─── Mahasiswa 4: Sudah ada penempatan KPM & PPL ─────────────────────
        $u4 = User::create([
            'name'     => 'Dewi Lengkap KPM PPL',
            'email'    => 'dewi.keduanya@kampus.ac.id',
            'password' => 'password123',
            'role'     => 'mahasiswa',
        ]);
        $p4 = MahasiswaProfile::create([
            'user_id'             => $u4->id,
            'nim'                 => '2110004',
            'phone'               => '084444444444',
            'address'             => 'Jl. Tebet Raya No. 22, Jakarta Selatan',
            'latitude'            => -6.2295,
            'longitude'           => 106.8543,
            'microteaching_grade' => 'A',
            'status'              => 'approved',
            'reviewed_at'         => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p4->id,
            'school_id'            => $createdSchools['MTs Al-Falah']->id,
            'gelombang_id'         => $gelombangKpm->id,
            'program'              => 'KPM',
            'distance_km'          => 0.00,
            'status'               => 'approved',
            'confirmed_at'         => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p4->id,
            'school_id'            => $createdSchools['MAN 1 Jakarta']->id,
            'gelombang_id'         => $gelombangPpl->id,
            'program'              => 'PPL',
            'distance_km'          => 1.71,
            'status'               => 'approved',
            'confirmed_at'         => now(),
        ]);
    }
}
