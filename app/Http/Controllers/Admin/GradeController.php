<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassAssignment;
use App\Models\Gelombang;
use App\Models\MahasiswaProfile;
use App\Models\Registration;
use App\Models\Submission;

class GradeController extends Controller
{
    public function index()
    {
        $program   = request('program', 'KPM');
        $gelombangId = request('gelombang_id');

        $query = MahasiswaProfile::with([
                'user',
                'registrations.school',
                'registrations.gelombang',
            ])
            ->whereHas('registrations', function ($q) use ($program, $gelombangId) {
                $q->where('program', $program)
                  ->where('status', 'approved');
                if ($gelombangId) {
                    $q->where('gelombang_id', $gelombangId);
                }
            })
            ->where('status', 'approved');

        $profiles = $query->paginate(20)->withQueryString();

        $assignments = ClassAssignment::orderBy('deadline')->get();
        $gelombangList = Gelombang::where('program', $program)->orderByDesc('tahun_akademik')->orderByDesc('nomor')->get();

        return view('admin.class.grades', compact('profiles', 'assignments', 'gelombangList', 'program', 'gelombangId'));
    }
}
