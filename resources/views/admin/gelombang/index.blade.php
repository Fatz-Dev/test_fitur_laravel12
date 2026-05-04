@extends('layouts.app')
@section('title', 'Gelombang KPM & PPL')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Gelombang KPM &amp; PPL</h1>
    <a href="{{ route('admin.gelombang.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded">
        + Tambah Gelombang
    </a>
</div>

@foreach(['KPM','PPL'] as $prog)
<div class="mb-6">
    <h2 class="font-semibold text-slate-700 mb-2">Program {{ $prog }}</h2>
    <div class="bg-white border border-slate-200 rounded overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-500 border-b bg-slate-50">
                <tr>
                    <th class="px-4 py-2">Gelombang</th>
                    <th>Tahun Akademik</th>
                    <th>Tanggal Buka</th>
                    <th>Tanggal Tutup</th>
                    <th>Status</th>
                    <th>Penempatan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y">
            @forelse($gelombang->where('program', $prog) as $g)
                <tr>
                    <td class="px-4 py-2 font-semibold">Gelombang {{ $g->nomor }}</td>
                    <td>{{ $g->tahun_akademik }}</td>
                    <td class="text-slate-600">{{ $g->tanggal_buka ? $g->tanggal_buka->format('d M Y') : '-' }}</td>
                    <td class="text-slate-600">{{ $g->tanggal_tutup ? $g->tanggal_tutup->format('d M Y') : '-' }}</td>
                    <td>
                        @if($g->is_active && $g->isOpen())
                            <span class="text-xs px-2 py-1 rounded bg-emerald-100 text-emerald-700">Aktif &amp; Terbuka</span>
                        @elseif($g->is_active)
                            <span class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-700">Aktif (Tutup)</span>
                        @else
                            <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-500">Tidak Aktif</span>
                        @endif
                    </td>
                    <td class="text-slate-600">{{ $g->registrations->count() }} mahasiswa</td>
                    <td class="text-right pr-4 space-x-2">
                        @if(!$g->is_active)
                            <form method="POST" action="{{ route('admin.gelombang.activate', $g) }}" class="inline">
                                @csrf
                                <button class="text-xs text-emerald-600 hover:underline">Aktifkan</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.gelombang.edit', $g) }}" class="text-xs text-indigo-600 hover:underline">Edit</a>
                        @if($g->registrations->isEmpty())
                            <form method="POST" action="{{ route('admin.gelombang.destroy', $g) }}" class="inline"
                                  onsubmit="return confirm('Hapus gelombang ini?');">
                                @csrf @method('DELETE')
                                <button class="text-xs text-rose-600 hover:underline">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-4 text-slate-500 text-xs">Belum ada gelombang {{ $prog }}.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endsection
