<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gelombang;
use Illuminate\Http\Request;

class GelombangController extends Controller
{
    public function index()
    {
        $gelombang = Gelombang::orderBy('program')->orderByDesc('tahun_akademik')->orderByDesc('nomor')->get();

        return view('admin.gelombang.index', compact('gelombang'));
    }

    public function create()
    {
        return view('admin.gelombang.form', ['gelombang' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'program'       => ['required', 'in:KPM,PPL'],
            'nomor'         => ['required', 'integer', 'min:1', 'max:99'],
            'tahun_akademik'=> ['required', 'string', 'max:20'],
            'tanggal_buka'  => ['nullable', 'date'],
            'tanggal_tutup' => ['nullable', 'date', 'after_or_equal:tanggal_buka'],
            'is_active'     => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            Gelombang::where('program', $data['program'])->update(['is_active' => false]);
        }

        Gelombang::create($data);

        return redirect()->route('admin.gelombang.index')
            ->with('status', 'Gelombang berhasil ditambahkan.');
    }

    public function edit(Gelombang $gelombang)
    {
        return view('admin.gelombang.form', compact('gelombang'));
    }

    public function update(Request $request, Gelombang $gelombang)
    {
        $data = $request->validate([
            'program'       => ['required', 'in:KPM,PPL'],
            'nomor'         => ['required', 'integer', 'min:1', 'max:99'],
            'tahun_akademik'=> ['required', 'string', 'max:20'],
            'tanggal_buka'  => ['nullable', 'date'],
            'tanggal_tutup' => ['nullable', 'date', 'after_or_equal:tanggal_buka'],
            'is_active'     => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_active']) {
            Gelombang::where('program', $data['program'])
                ->where('id', '!=', $gelombang->id)
                ->update(['is_active' => false]);
        }

        $gelombang->update($data);

        return redirect()->route('admin.gelombang.index')
            ->with('status', 'Gelombang berhasil diperbarui.');
    }

    public function destroy(Gelombang $gelombang)
    {
        if ($gelombang->registrations()->exists()) {
            return back()->withErrors(['gelombang' => 'Gelombang tidak dapat dihapus karena sudah memiliki data penempatan.']);
        }
        $gelombang->delete();

        return back()->with('status', 'Gelombang dihapus.');
    }

    public function activate(Gelombang $gelombang)
    {
        Gelombang::where('program', $gelombang->program)->update(['is_active' => false]);
        $gelombang->update(['is_active' => true]);

        return back()->with('status', "{$gelombang->label()} ({$gelombang->program}) diaktifkan.");
    }
}
