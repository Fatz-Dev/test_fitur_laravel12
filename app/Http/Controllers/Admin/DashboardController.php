<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaProfile;
use App\Models\Registration;
use App\Models\School;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'mahasiswa_total' => MahasiswaProfile::count(),
            'mahasiswa_pending' => MahasiswaProfile::where('status', 'pending')->count(),
            'mahasiswa_approved' => MahasiswaProfile::where('status', 'approved')->count(),
            'schools' => School::count(),
            'registrations_kpm' => Registration::where('program', 'KPM')->count(),
            'registrations_ppl' => Registration::where('program', 'PPL')->count(),
            'registrations_pending' => Registration::where('status', 'pending')->count(),
        ];

        $recentMahasiswa = MahasiswaProfile::with('user')
            ->latest()->take(5)->get();

        $recentRegistrations = Registration::with(['mahasiswaProfile.user', 'school'])
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentMahasiswa', 'recentRegistrations'));
    }
}
