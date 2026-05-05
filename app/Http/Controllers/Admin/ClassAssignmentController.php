<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClassAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassAssignment::with('creator')
            ->withCount('submissions')
            ->latest();

        if ($request->filled('program')) {
            $prog = $request->program;
            $query->where(function ($q) use ($prog) {
                $q->where('program', $prog)->orWhereNull('program');
            });
        }

        $assignments = $query->paginate(15)->withQueryString();
        $filterProgram = $request->program;

        return view('admin.class.assignments.index', compact('assignments', 'filterProgram'));
    }

    public function create()
    {
        return view('admin.class.assignments.form', ['assignment' => new ClassAssignment()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('assignments', 'public');
        }

        ClassAssignment::create([
            ...$data,
            'attachment_path' => $path,
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('admin.class.assignments.index')
            ->with('status', 'Tugas berhasil ditambahkan.');
    }

    public function edit(ClassAssignment $assignment)
    {
        return view('admin.class.assignments.form', compact('assignment'));
    }

    public function update(Request $request, ClassAssignment $assignment)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment_path) {
                Storage::disk('public')->delete($assignment->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment->update($data);

        return redirect()->route('admin.class.assignments.index')
            ->with('status', 'Tugas berhasil diperbarui.');
    }

    public function destroy(ClassAssignment $assignment)
    {
        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }
        $assignment->delete();

        return back()->with('status', 'Tugas dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'deadline'     => ['required', 'date'],
            'program'      => ['nullable', 'in:KPM,PPL'],
            'attachment'   => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);
    }
}
