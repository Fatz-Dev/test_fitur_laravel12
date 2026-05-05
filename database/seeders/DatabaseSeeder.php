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
        Registration::query()->delete();
        MahasiswaProfile::query()->delete();
        User::query()->delete();
        School::query()->delete();
        Gelombang::query()->delete();
        DB::table('settings')->delete();

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

        // ─── Lokasi KPM (Desa) ────────────────────────────────────────────────
        $desaCempaka = School::create([
            'name' => 'Desa Cempaka Putih', 'jenjang' => 'Kelurahan',
            'address' => 'Jl. Cempaka Putih Tengah, Jakarta Pusat',
            'latitude' => -6.1722, 'longitude' => 106.8730,
            'program' => 'KPM', 'kuota_kpm' => 8, 'kuota_ppl' => 0, 'is_active' => true,
        ]);
        $desaTebet = School::create([
            'name' => 'Kelurahan Tebet Barat', 'jenjang' => 'Kelurahan',
            'address' => 'Jl. Tebet Barat Dalam, Jakarta Selatan',
            'latitude' => -6.2295, 'longitude' => 106.8543,
            'program' => 'KPM', 'kuota_kpm' => 6, 'kuota_ppl' => 0, 'is_active' => true,
        ]);
        School::create([
            'name' => 'Desa Margahayu Bekasi', 'jenjang' => 'Desa',
            'address' => 'Jl. Cut Mutia, Bekasi Timur',
            'latitude' => -6.2349, 'longitude' => 106.9896,
            'program' => 'KPM', 'kuota_kpm' => 5, 'kuota_ppl' => 0, 'is_active' => true,
        ]);

        // ─── Lokasi PPL (Sekolah) ─────────────────────────────────────────────
        $sman3 = School::create([
            'name' => 'SMAN 03 Jakarta Timur', 'jenjang' => 'SMA',
            'address' => 'Jl. Matraman Raya No. 88, Jakarta Timur',
            'latitude' => -6.2088, 'longitude' => 106.8519,
            'program' => 'PPL', 'kuota_kpm' => 0, 'kuota_ppl' => 6, 'is_active' => true,
        ]);
        School::create([
            'name' => 'SMKN 4 Jakarta Barat', 'jenjang' => 'SMK',
            'address' => 'Jl. Daan Mogot No. 12, Jakarta Barat',
            'latitude' => -6.1683, 'longitude' => 106.7589,
            'program' => 'PPL', 'kuota_kpm' => 0, 'kuota_ppl' => 5, 'is_active' => true,
        ]);
        $man1 = School::create([
            'name' => 'MAN 1 Jakarta', 'jenjang' => 'MA',
            'address' => 'Jl. Asem Baris No. 9, Jakarta Selatan',
            'latitude' => -6.2440, 'longitude' => 106.8453,
            'program' => 'PPL', 'kuota_kpm' => 0, 'kuota_ppl' => 5, 'is_active' => true,
        ]);

        // ─── Lokasi BOTH (Sekolah yang juga menerima KPM) ─────────────────────
        School::create([
            'name' => 'SDN 01 Jakarta Pusat', 'jenjang' => 'SD',
            'address' => 'Jl. Merdeka Selatan No. 1, Jakarta Pusat',
            'latitude' => -6.1751, 'longitude' => 106.8650,
            'program' => 'BOTH', 'kuota_kpm' => 4, 'kuota_ppl' => 4, 'is_active' => true,
        ]);
        School::create([
            'name' => 'SMPN 02 Jakarta Selatan', 'jenjang' => 'SMP',
            'address' => 'Jl. Kebon Jeruk Raya, Jakarta Selatan',
            'latitude' => -6.2615, 'longitude' => 106.8106,
            'program' => 'BOTH', 'kuota_kpm' => 3, 'kuota_ppl' => 3, 'is_active' => true,
        ]);

        // ─── Mahasiswa 1: KPM saja — belum ada penempatan ─────────────────────
        $u1 = User::create([
            'name' => 'Andi KPM Saja', 'email' => 'andi.kosong@kampus.ac.id',
            'password' => 'password123', 'role' => 'mahasiswa',
        ]);
        MahasiswaProfile::create([
            'user_id' => $u1->id, 'nim' => '2110001',
            'program_choice' => 'KPM',
            'phone' => '081111111111',
            'address' => 'Jl. Merdeka Barat No. 10, Jakarta Pusat',
            'latitude' => -6.1751, 'longitude' => 106.8650,
            'microteaching_grade' => 'A', 'status' => 'approved', 'reviewed_at' => now(),
        ]);

        // ─── Mahasiswa 2: KPM → ditempatkan di desa ───────────────────────────
        $u2 = User::create([
            'name' => 'Budi Nur KPM', 'email' => 'budi.kpm@kampus.ac.id',
            'password' => '
            ', 'role' => 'mahasiswa',
        ]);
        $p2 = MahasiswaProfile::create([
            'user_id' => $u2->id, 'nim' => '2110002',
            'program_choice' => 'KPM',
            'phone' => '082222222222',
            'address' => 'Jl. Cempaka Putih No. 5, Jakarta Pusat',
            'latitude' => -6.1722, 'longitude' => 106.8730,
            'microteaching_grade' => 'B', 'status' => 'approved', 'reviewed_at' => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p2->id, 'school_id' => $desaCempaka->id,
            'gelombang_id' => $gelombangKpm->id, 'program' => 'KPM',
            'distance_km' => 0.03, 'status' => 'pending',
        ]);

        // ─── Mahasiswa 3: PPL → ditempatkan di sekolah ────────────────────────
        $u3 = User::create([
            'name' => 'Citra Hanya PPL', 'email' => 'citra.ppl@kampus.ac.id',
            'password' => 'password123', 'role' => 'mahasiswa',
        ]);
        $p3 = MahasiswaProfile::create([
            'user_id' => $u3->id, 'nim' => '2110003',
            'program_choice' => 'PPL',
            'phone' => '083333333333',
            'address' => 'Jl. Matraman Raya No. 10, Jakarta Timur',
            'latitude' => -6.2088, 'longitude' => 106.8519,
            'microteaching_grade' => 'A', 'status' => 'approved', 'reviewed_at' => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p3->id, 'school_id' => $sman3->id,
            'gelombang_id' => $gelombangPpl->id, 'program' => 'PPL',
            'distance_km' => 0.00, 'status' => 'pending',
        ]);

        // ─── Mahasiswa 4: PKPPM → desa + sekolah berdekatan (approved) ─────────
        $u4 = User::create([
            'name' => 'Dewi PKPPM Lengkap', 'email' => 'dewi.keduanya@kampus.ac.id',
            'password' => 'password123', 'role' => 'mahasiswa',
        ]);
        $p4 = MahasiswaProfile::create([
            'user_id' => $u4->id, 'nim' => '2110004',
            'program_choice' => 'PKPPM',
            'phone' => '084444444444',
            'address' => 'Jl. Tebet Barat Dalam No. 12, Jakarta Selatan',
            'latitude' => -6.2295, 'longitude' => 106.8543,
            'microteaching_grade' => 'A', 'status' => 'approved', 'reviewed_at' => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p4->id, 'school_id' => $desaTebet->id,
            'gelombang_id' => $gelombangKpm->id, 'program' => 'KPM',
            'distance_km' => 0.00, 'status' => 'approved', 'confirmed_at' => now(),
        ]);
        Registration::create([
            'mahasiswa_profile_id' => $p4->id, 'school_id' => $man1->id,
            'gelombang_id' => $gelombangPpl->id, 'program' => 'PPL',
            'distance_km' => 1.71, 'status' => 'approved', 'confirmed_at' => now(),
        ]);
    }
}
