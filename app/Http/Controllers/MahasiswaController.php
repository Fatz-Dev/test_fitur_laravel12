<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\MahasiswaProfile;
use App\Models\Registration;
use App\Models\Setting;
use App\Support\Geo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller
{
    public function dashboard()
    {
        $profile = Auth::user()->mahasiswaProfile;
        $registrations = $profile
            ? $profile->registrations()->with('school', 'gelombang')->get()
            : collect();

        $gelombang = [
            'KPM' => Gelombang::activeFor('KPM'),
            'PPL' => Gelombang::activeFor('PPL'),
        ];

        return view('mahasiswa.dashboard', compact('profile', 'registrations', 'gelombang'));
    }

    public function createProfile()
    {
        $profile = Auth::user()->mahasiswaProfile;
        if ($profile) {
            return redirect()->route('mahasiswa.dashboard');
        }

        return view('mahasiswa.profile-create');
    }

    public function storeProfile(Request $request)
    {
        if (Auth::user()->mahasiswaProfile) {
            return redirect()->route('mahasiswa.dashboard');
        }

        $data = $request->validate([
            'nim'                => ['required', 'string', 'max:30', 'unique:mahasiswa_profiles,nim'],
            'phone'              => ['nullable', 'string', 'max:30'],
            'address'            => ['required', 'string'],
            'latitude'           => ['required', 'numeric', 'between:-90,90'],
            'longitude'          => ['required', 'numeric', 'between:-180,180'],
            'microteaching_grade'=> ['required', 'in:A,B,C,D,E'],
            'transkrip'          => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'ktm'                => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'surat_pengantar'    => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'pas_foto'           => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $userId = Auth::id();
        $paths  = [
            'transkrip_path'       => $request->file('transkrip')->store("mahasiswa/$userId", 'public'),
            'ktm_path'             => $request->file('ktm')->store("mahasiswa/$userId", 'public'),
            'surat_pengantar_path' => $request->file('surat_pengantar')->store("mahasiswa/$userId", 'public'),
            'pas_foto_path'        => $request->file('pas_foto')->store("mahasiswa/$userId", 'public'),
        ];

        MahasiswaProfile::create([
            'user_id'             => $userId,
            'nim'                 => $data['nim'],
            'phone'               => $data['phone'] ?? null,
            'address'             => $data['address'],
            'latitude'            => $data['latitude'],
            'longitude'           => $data['longitude'],
            'microteaching_grade' => $data['microteaching_grade'],
            ...$paths,
            'status' => 'pending',
        ]);

        return redirect()->route('mahasiswa.dashboard')
            ->with('status', 'Pendaftaran Anda menunggu review admin. Setelah disetujui, sistem akan otomatis menetapkan penempatan KPM dan PPL.');
    }

    /**
     * Update koordinat domisili mahasiswa (hanya saat profil masih pending).
     */
    public function updateLocation(Request $request)
    {
        $profile = Auth::user()->mahasiswaProfile;
        abort_unless($profile, 403, 'Profil tidak ditemukan.');
        abort_unless($profile->status === 'pending', 422, 'Lokasi hanya dapat diubah saat profil masih menunggu review.');

        $data = $request->validate([
            'address'   => ['required', 'string'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $profile->update([
            'address'   => $data['address'],
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        return back()->with('status', 'Lokasi domisili berhasil diperbarui.');
    }

    public function cancelRegistration(Registration $registration)
    {
        $profile = Auth::user()->mahasiswaProfile;
        abort_unless($profile && $registration->mahasiswa_profile_id === $profile->id, 403);
        abort_if($registration->status === 'approved', 422, 'Pendaftaran sudah disetujui dan tidak dapat dibatalkan.');

        $registration->delete();

        return back()->with('status', 'Pendaftaran dibatalkan.');
    }
}
