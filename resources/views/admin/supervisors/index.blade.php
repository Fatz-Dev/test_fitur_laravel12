@extends('layouts.app')
@section('title', 'Manajemen Supervisor')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Supervisor</h2>
        <p class="font-body-sm text-on-surface-variant">Kelola akun supervisor dan penugasan ke lokasi KPM/PPL</p>
    </div>
    <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="inline-flex items-center gap-2 bg-primary text-white text-sm px-md py-2 rounded-lg hover:bg-primary-container transition-colors font-label-md">
        <i class="ti ti-user-plus text-[18px]"></i>
        Tambah Supervisor
    </button>
</div>

{{-- Assign school --}}
<div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm mb-lg">
    <h3 class="font-label-md text-on-surface mb-md flex items-center gap-2">
        <i class="ti ti-building-school text-primary text-[18px]"></i>
        Penugasan Supervisor ke Lokasi
    </h3>
    <form method="POST" action="{{ route('admin.supervisors.assign') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div class="space-y-xs">
            <label class="font-label-sm text-on-surface-variant block">Lokasi</label>
            <select name="school_id" required
                    class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30 w-64">
                <option value="">— Pilih lokasi —</option>
                @foreach($schools as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->name }} ({{ $sc->program }})</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-xs">
            <label class="font-label-sm text-on-surface-variant block">Supervisor</label>
            <select name="supervisor_id"
                    class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30 w-52">
                <option value="">— Hapus supervisor —</option>
                @foreach($supervisors as $sv)
                    <option value="{{ $sv->id }}">{{ $sv->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="bg-secondary text-white px-lg py-2 rounded-lg font-label-md text-sm hover:opacity-90 transition-opacity">
            Simpan
        </button>
    </form>
</div>

{{-- Supervisor list --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                <tr>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Supervisor</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Email</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Lokasi Ditangani</th>
                    <th class="px-md py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($supervisors as $sv)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-md py-3">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($sv->name, 0, 1)) }}
                            </div>
                            <span class="font-label-md text-on-surface">{{ $sv->name }}</span>
                        </div>
                    </td>
                    <td class="px-md py-3 text-on-surface-variant text-[13px]">{{ $sv->email }}</td>
                    <td class="px-md py-3">
                        @if($sv->supervisorSchools->isEmpty())
                            <span class="text-[12px] text-outline italic">Belum ditugaskan</span>
                        @else
                            <div class="flex flex-wrap gap-1">
                                @foreach($sv->supervisorSchools as $sc)
                                    <span class="text-[11px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-medium">
                                        {{ $sc->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-md py-3 text-right">
                        <form method="POST" action="{{ route('admin.supervisors.destroy', $sv) }}"
                              onsubmit="return confirm('Hapus akun supervisor {{ $sv->name }}?')">
                            @csrf @method('DELETE')
                            <button class="text-[12px] text-error hover:underline font-medium">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-12 text-on-surface-variant">
                        <i class="ti ti-user-off text-[48px] opacity-30 block mb-2"></i>
                        <p class="font-body-sm">Belum ada supervisor. Tambahkan supervisor baru.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-md">{{ $supervisors->links() }}</div>

{{-- Modal Tambah Supervisor --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md border border-outline-variant">
        <div class="flex justify-between items-center px-xl py-lg border-b border-outline-variant">
            <h3 class="font-h3 text-on-surface" style="font-size:18px">Tambah Akun Supervisor</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-outline hover:text-on-surface p-1 rounded-lg hover:bg-slate-100">
                <i class="ti ti-x text-[20px]"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.supervisors.store') }}" class="px-xl py-lg space-y-md">
            @csrf
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Nama Lengkap</label>
                <input name="name" type="text" required placeholder="Nama supervisor"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary outline-none">
            </div>
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Email</label>
                <input name="email" type="email" required placeholder="email@kampus.ac.id"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary outline-none">
            </div>
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Password</label>
                <input name="password" type="password" required placeholder="Min 6 karakter"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="text-sm px-lg py-2 border border-outline-variant rounded-lg hover:bg-slate-50 font-label-md">Batal</button>
                <button type="submit"
                        class="text-sm bg-primary hover:bg-primary-container text-white px-lg py-2 rounded-lg font-label-md transition-colors">
                    Tambah Supervisor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
