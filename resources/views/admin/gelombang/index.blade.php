@extends('layouts.app')
@section('title', 'Gelombang KPM & PPL')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Gelombang KPM &amp; PPL</h2>
        <p class="font-body-sm text-on-surface-variant">Kelola periode pendaftaran per program</p>
    </div>
    <a href="{{ route('admin.gelombang.create') }}"
       class="bg-primary text-white text-sm px-md py-2 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2 font-label-md">
        <i class="ti ti-plus text-[18px]"></i>
        Tambah Gelombang
    </a>
</div>

@foreach(['KPM','PPL'] as $prog)
<div class="mb-lg">
    <div class="flex items-center gap-2 mb-md">
        <i class="ti ti-calendar text-[18px] {{ $prog === 'KPM' ? 'text-amber-600' : 'text-blue-600' }}"></i>
        <h3 class="font-label-md text-on-surface uppercase tracking-wider text-[12px]">Program {{ $prog }}</h3>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                    <tr>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Gelombang</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Tahun Akademik</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Tanggal Buka</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Tanggal Tutup</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Status</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Penempatan</th>
                        <th class="px-md py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($gelombang->where('program', $prog) as $g)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-md py-3 font-label-md text-on-surface">Gelombang {{ $g->nomor }}</td>
                        <td class="px-md py-3 text-[12px] text-on-surface-variant">{{ $g->tahun_akademik }}</td>
                        <td class="px-md py-3 text-[12px] text-on-surface-variant">
                            {{ $g->tanggal_buka ? $g->tanggal_buka->format('d M Y') : '-' }}
                        </td>
                        <td class="px-md py-3 text-[12px] text-on-surface-variant">
                            {{ $g->tanggal_tutup ? $g->tanggal_tutup->format('d M Y') : '-' }}
                        </td>
                        <td class="px-md py-3">
                            @if($g->is_active && $g->isOpen())
                                <span class="text-[12px] px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium flex items-center gap-1 w-fit">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Aktif &amp; Terbuka
                                </span>
                            @elseif($g->is_active)
                                <span class="text-[12px] px-2 py-1 rounded-full bg-amber-100 text-amber-700 font-medium">Aktif (Tutup)</span>
                            @else
                                <span class="text-[12px] px-2 py-1 rounded-full bg-slate-100 text-slate-500 font-medium">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-md py-3 text-[12px] text-on-surface-variant">{{ $g->registrations->count() }} mahasiswa</td>
                        <td class="px-md py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @if(!$g->is_active)
                                    <form method="POST" action="{{ route('admin.gelombang.activate', $g) }}" class="inline">
                                        @csrf
                                        <button class="text-[12px] text-secondary hover:underline font-medium">Aktifkan</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.gelombang.edit', $g) }}" class="text-[12px] text-primary hover:underline font-medium">Edit</a>
                                @if($g->registrations->isEmpty())
                                    <form method="POST" action="{{ route('admin.gelombang.destroy', $g) }}" class="inline"
                                          onsubmit="return confirm('Hapus gelombang ini?');">
                                        @csrf @method('DELETE')
                                        <button class="text-[12px] text-error hover:underline font-medium">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-on-surface-variant">
                            <p class="font-body-sm">Belum ada gelombang {{ $prog }}.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach
@endsection
