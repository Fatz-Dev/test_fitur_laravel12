<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ClassAssignment;
use App\Models\Registration;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function classList()
    {
        $profile = Auth::user()->mahasiswaProfile;

        if (! $profile || ! $profile->isApproved()) {
            return view('mahasiswa.class.index', ['classes' => collect(), 'profile' => $profile]);
        }

        $classes = $profile->registrations()
            ->with('school', 'gelombang')
            ->where('status', 'approved')
            ->get();

        $assignmentCount = ClassAssignment::count();

        return view('mahasiswa.class.index', compact('classes', 'profile', 'assignmentCount'));
    }

    public function assignments(Registration $registration)
    {
        $profile = Auth::user()->mahasiswaProfile;
        abort_unless($profile && $registration->mahasiswa_profile_id === $profile->id, 403);
        abort_unless($registration->status === 'approved', 403);

        $assignments = ClassAssignment::orderBy('deadline')->get();

        $submissions = Submission::where('mahasiswa_profile_id', $profile->id)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        return view('mahasiswa.class.assignments', compact('registration', 'assignments', 'submissions', 'profile'));
    }

    public function submissionDetail(Registration $registration, ClassAssignment $assignment)
    {
        $profile = Auth::user()->mahasiswaProfile;
        abort_unless($profile && $registration->mahasiswa_profile_id === $profile->id, 403);
        abort_unless($registration->status === 'approved', 403);

        $submission = Submission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'mahasiswa_profile_id' => $profile->id],
        );

        return view('mahasiswa.class.submission', compact('registration', 'assignment', 'submission', 'profile'));
    }

    public function submit(Request $request, Registration $registration, ClassAssignment $assignment)
    {
        $profile = Auth::user()->mahasiswaProfile;
        abort_unless($profile && $registration->mahasiswa_profile_id === $profile->id, 403);
        abort_unless($registration->status === 'approved', 403);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
            'file'  => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,zip', 'max:10240'],
        ]);

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('mahasiswa_profile_id', $profile->id)
            ->firstOrFail();

        $filePath = $submission->file_path;
        if ($request->hasFile('file')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store("submissions/{$profile->id}", 'public');
        }

        $submission->update([
            'file_path'    => $filePath,
            'notes'        => $data['notes'] ?? null,
            'submitted_at' => now(),
        ]);

        return back()->with('status', 'Tugas berhasil dikumpulkan.');
    }
}
