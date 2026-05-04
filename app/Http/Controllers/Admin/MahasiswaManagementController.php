<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MahasiswaProfile;
use App\Services\AutoAssignService;
use Illuminate\Http\Request;

class MahasiswaManagementController extends Controller
{
    public function __construct(private AutoAssignService $autoAssign) {}

    public function index(Request $request)
    {
        $query = MahasiswaProfile::with('user');
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($q = $request->get('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('nim', 'like', "%$q%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"));
            });
        }
        $mahasiswas = $query->latest()->paginate(15)->withQueryString();

        return view('admin.mahasiswa.index', compact('mahasiswas'));
    }

    public function show(MahasiswaProfile $mahasiswa)
    {
        $mahasiswa->load('user', 'registrations.school');

        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    public function approve(Request $request, MahasiswaProfile $mahasiswa)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string']]);

        $mahasiswa->update([
            'status'     => 'approved',
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_at'=> now(),
        ]);

        // Otomatis tetapkan sekolah KPM & PPL setelah disetujui
        $results = $this->autoAssign->assign($mahasiswa);

        $assigned = collect($results)
            ->filter(fn ($v) => $v !== null)
            ->map(fn ($v, $k) => "$k: {$v['school']->name} (via {$v['method']})")
            ->values()
            ->implode(', ');

        $msg = 'Mahasiswa disetujui.';
        if ($assigned) {
            $msg .= " Penempatan otomatis: $assigned.";
        } else {
            $msg .= ' Tidak ada sekolah dengan kuota tersedia untuk ditetapkan.';
        }

        return back()->with('status', $msg);
    }

    public function reject(Request $request, MahasiswaProfile $mahasiswa)
    {
        $data = $request->validate(['admin_note' => ['required', 'string']]);
        $mahasiswa->update([
            'status'      => 'rejected',
            'admin_note'  => $data['admin_note'],
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Mahasiswa ditolak.');
    }

    public function destroy(MahasiswaProfile $mahasiswa)
    {
        $user = $mahasiswa->user;
        $mahasiswa->delete();
        $user?->delete();

        return redirect()->route('admin.mahasiswa.index')->with('status', 'Data mahasiswa dihapus.');
    }
}
