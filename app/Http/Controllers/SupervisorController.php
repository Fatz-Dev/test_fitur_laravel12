<?php

namespace App\Http\Controllers;

use App\Models\ClassAssignment;
use App\Models\MahasiswaProfile;
use App\Models\Registration;
use App\Models\School;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
    public function dashboard()
    {
        $schools = Auth::user()->supervisorSchools()->with('registrations.mahasiswaProfile.user')->get();
        $assignmentCount = ClassAssignment::count();
        $totalMahasiswa = 0;
        foreach ($schools as $school) {
            $totalMahasiswa += $school->registrations()->where('status', 'approved')->count();
        }
        $pendingGrades = Submission::whereNull('grade')
            ->whereNotNull('submitted_at')
            ->whereHas('mahasiswaProfile.registrations', function ($q) use ($schools) {
                $q->whereIn('school_id', $schools->pluck('id'))->where('status', 'approved');
            })
            ->count();

        return view('supervisor.dashboard', compact('schools', 'assignmentCount', 'totalMahasiswa', 'pendingGrades'));
    }

    public function classDetail(School $school)
    {
        $this->authorizeSchool($school);

        $registrations = $school->registrations()
            ->with('mahasiswaProfile.user', 'gelombang')
            ->where('status', 'approved')
            ->orderBy('program')
            ->get();

        $assignments = $this->assignmentsForSchool($school)->get();

        return view('supervisor.class-detail', compact('school', 'registrations', 'assignments'));
    }

    public function studentAssignments(School $school, MahasiswaProfile $profile)
    {
        $this->authorizeSchool($school);

        $reg = $school->registrations()
            ->where('mahasiswa_profile_id', $profile->id)
            ->where('status', 'approved')
            ->firstOrFail();

        $assignments = $this->assignmentsForProgram($reg->program)->get();

        $submissions = Submission::where('mahasiswa_profile_id', $profile->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return view('supervisor.student-assignments', compact('school', 'profile', 'reg', 'assignments', 'submissions'));
    }

    public function submissionDetail(School $school, MahasiswaProfile $profile, ClassAssignment $assignment)
    {
        $this->authorizeSchool($school);

        $reg = $school->registrations()
            ->where('mahasiswa_profile_id', $profile->id)
            ->where('status', 'approved')
            ->firstOrFail();

        $submission = Submission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'mahasiswa_profile_id' => $profile->id],
        );

        return view('supervisor.submission', compact('school', 'profile', 'assignment', 'submission', 'reg'));
    }

    private function assignmentsForSchool(School $school): \Illuminate\Database\Eloquent\Builder
    {
        return $this->assignmentsForProgram($school->program);
    }

    private function assignmentsForProgram(?string $program): \Illuminate\Database\Eloquent\Builder
    {
        $q = ClassAssignment::orderBy('deadline');
        if ($program) {
            $q->where(function ($sq) use ($program) {
                $sq->whereNull('program')->orWhere('program', $program);
            });
        }
        return $q;
    }

    public function gradeSubmission(Request $request, School $school, MahasiswaProfile $profile, ClassAssignment $assignment)
    {
        $this->authorizeSchool($school);

        $data = $request->validate([
            'grade'   => ['required', 'integer', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_profile_id', $profile->id)
            ->firstOrFail();

        $submission->update([
            'grade'      => $data['grade'],
            'comment'    => $data['comment'] ?? null,
            'graded_by'  => Auth::id(),
            'graded_at'  => now(),
        ]);

        return back()->with('status', 'Penilaian berhasil disimpan.');
    }

    private function authorizeSchool(School $school): void
    {
        abort_unless(
            $school->supervisor_id === Auth::id(),
            403,
            'Anda tidak berwenang mengakses kelas ini.'
        );
    }
}
