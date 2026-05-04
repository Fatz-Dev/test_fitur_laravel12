<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::latest()->paginate(15);

        return view('admin.schools.index', compact('schools'));
    }

    public function create()
    {
        return view('admin.schools.form', ['school' => new School()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        School::create($data);

        return redirect()->route('admin.schools.index')->with('status', 'Sekolah ditambahkan.');
    }

    public function edit(School $school)
    {
        return view('admin.schools.form', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $data = $this->validateData($request);
        $school->update($data);

        return redirect()->route('admin.schools.index')->with('status', 'Sekolah diperbarui.');
    }

    public function destroy(School $school)
    {
        $school->delete();

        return back()->with('status', 'Sekolah dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'jenjang' => ['required', 'in:SD,SMP,SMA,SMK,MI,MTs,MA'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'program' => ['required', 'in:KPM,PPL,BOTH'],
            'kuota_kpm' => ['required', 'integer', 'min:0'],
            'kuota_ppl' => ['required', 'integer', 'min:0'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
