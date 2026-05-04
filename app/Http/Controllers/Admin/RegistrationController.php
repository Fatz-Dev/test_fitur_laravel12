<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gelombang;
use App\Models\Registration;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['mahasiswaProfile.user', 'school', 'gelombang']);

        if ($program = $request->get('program')) {
            $query->where('program', $program);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($gelombangId = $request->get('gelombang_id')) {
            $query->where('gelombang_id', $gelombangId);
        }

        $registrations = $query->latest()->paginate(20)->withQueryString();
        $gelombangList = Gelombang::orderBy('program')->orderByDesc('tahun_akademik')->orderByDesc('nomor')->get();

        return view('admin.registrations.index', compact('registrations', 'gelombangList'));
    }

    public function approve(Registration $registration)
    {
        $registration->update([
            'status'       => 'approved',
            'confirmed_at' => now(),
        ]);

        return back()->with('status', 'Penempatan disetujui.');
    }

    public function reject(Request $request, Registration $registration)
    {
        $data = $request->validate(['note' => ['nullable', 'string']]);
        $registration->update([
            'status' => 'rejected',
            'note'   => $data['note'] ?? null,
        ]);

        return back()->with('status', 'Penempatan ditolak.');
    }

    public function destroy(Registration $registration)
    {
        $registration->delete();

        return back()->with('status', 'Penempatan dihapus.');
    }
}
