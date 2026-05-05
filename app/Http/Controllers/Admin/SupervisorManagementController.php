<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SupervisorManagementController extends Controller
{
    public function index()
    {
        $supervisors = User::where('role', 'supervisor')
            ->with('supervisorSchools')
            ->latest()
            ->paginate(15);

        $schools = School::whereNull('supervisor_id')->orWhereNotNull('supervisor_id')
            ->orderBy('name')
            ->get();

        return view('admin.supervisors.index', compact('supervisors', 'schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'supervisor',
        ]);

        return back()->with('status', 'Akun supervisor berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        abort_unless($user->role === 'supervisor', 403);
        $user->supervisorSchools()->update(['supervisor_id' => null]);
        $user->delete();

        return back()->with('status', 'Supervisor dihapus.');
    }

    public function assignSchool(Request $request)
    {
        $data = $request->validate([
            'school_id'     => ['required', 'exists:schools,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
        ]);

        $school = School::findOrFail($data['school_id']);
        $school->update(['supervisor_id' => $data['supervisor_id'] ?: null]);

        return back()->with('status', 'Penugasan supervisor diperbarui.');
    }
}
