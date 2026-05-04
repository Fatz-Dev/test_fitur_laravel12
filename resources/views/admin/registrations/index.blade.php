@extends('layouts.app')
@section('title', 'Penempatan')
@section('content')
<h1 class="text-2xl font-bold mb-4">Penempatan KPM &amp; PPL</h1>

<form method="GET" class="flex flex-wrap gap-2 mb-4">
    <select name="program" class="border border-slate-300 rounded px-3 py-2 text-sm">
        <option value="">Semua Program</option>
        @foreach(['KPM','PPL'] as $p)
            <option value="{{ $p }}" @selected(request('program')===$p)>{{ $p }}</option>
        @endforeach
    </select>
    <select name="gelombang_id" class="border border-slate-300 rounded px-3 py-2 text-sm">
        <option value="">Semua Gelombang</option>
        @foreach($gelombangList as $g)
            <option value="{{ $g->id }}" @selected(request('gelombang_id')==(string)$g->id)>
                {{ $g->program }} — {{ $g->label() }}
            </option>
        @endforeach
    </select>
    <select name="status" class="border border-slate-300 rounded px-3 py-2 text-sm">
        <option value="">Semua Status</option>
        @foreach(['pending','approved','rejected','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="bg-slate-700 text-white text-sm px-4 rounded hover:bg-slate-800">Filter</button>
</form>

<div class="bg-white border border-slate-200 rounded overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-xs text-slate-500 border-b bg-slate-50">
            <tr>
                <th class="px-3 py-2">Mahasiswa</th>
                <th>Program</th>
                <th>Gelombang</th>
                <th>Sekolah</th>
                <th>Jarak</th>
                <th>Status</th>
                <th>Diajukan</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y">
        @forelse($registrations as $r)
            <tr>
                <td class="px-3 py-2">
                    <p class="font-medium">{{ $r->mahasiswaProfile->user->name ?? '-' }}</p>
                    <p class="text-xs text-slate-500">{{ $r->mahasiswaProfile->nim }}</p>
                </td>
                <td><span class="text-xs px-2 py-1 rounded bg-indigo-50 text-indigo-700">{{ $r->program }}</span></td>
                <td class="text-xs text-slate-600">
                    {{ $r->gelombang ? $r->gelombang->label() : '-' }}
                </td>
                <td>{{ $r->school->name }}</td>
                <td>{{ number_format($r->distance_km, 2) }} km</td>
                <td>
                    @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose','cancelled'=>'slate'][$r->status]; @endphp
                    <span class="text-xs px-2 py-1 rounded bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">{{ $r->status }}</span>
                </td>
                <td class="text-xs text-slate-500">{{ $r->created_at->format('d M Y') }}</td>
                <td class="text-right pr-3 space-x-2">
                    @if($r->status === 'pending')
                        <form method="POST" action="{{ route('admin.registrations.approve', $r) }}" class="inline">
                            @csrf
                            <button class="text-xs text-emerald-600 hover:underline">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.registrations.reject', $r) }}" class="inline"
                              onsubmit="this.querySelector('[name=note]').value = prompt('Alasan penolakan?') || '';">
                            @csrf
                            <input type="hidden" name="note">
                            <button class="text-xs text-rose-600 hover:underline">Tolak</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.registrations.destroy', $r) }}" class="inline"
                          onsubmit="return confirm('Hapus penempatan?');">
                        @csrf @method('DELETE')
                        <button class="text-xs text-slate-500 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center py-6 text-slate-500">Belum ada penempatan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $registrations->links() }}</div>
@endsection
