@extends('layouts.app')
@section('title', 'Lokasi KPM & PPL')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Lokasi KPM &amp; PPL</h2>
        <p class="font-body-sm text-on-surface-variant">KPM &rarr; Desa &nbsp;|&nbsp; PPL &rarr; Sekolah</p>
    </div>
    <a href="{{ route('admin.schools.create') }}"
       class="bg-primary text-white text-sm px-md py-2 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2 font-label-md">
        <i class="ti ti-map-pin-plus text-[18px]"></i>
        Tambah Lokasi
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                <tr>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Nama Lokasi</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Tipe</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Jenjang</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Program</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Kuota KPM / PPL</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Koordinat</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Status</th>
                    <th class="px-md py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($schools as $s)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-md py-3">
                        <p class="font-label-md text-on-surface">{{ $s->name }}</p>
                    </td>
                    <td class="px-md py-3">
                        @if($s->program === 'KPM')
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-medium">Desa</span>
                        @else
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">Sekolah</span>
                        @endif
                    </td>
                    <td class="px-md py-3 text-[12px] text-on-surface-variant">{{ $s->jenjang ?: '-' }}</td>
                    <td class="px-md py-3">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $s->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $s->program }}
                        </span>
                    </td>
                    <td class="px-md py-3 text-[12px]">
                        <span class="{{ $s->program === 'KPM' ? 'text-amber-700 font-medium' : 'text-outline' }}">
                            {{ $s->program === 'KPM' ? $s->kuota_kpm : '-' }}
                        </span>
                        <span class="text-outline mx-1">/</span>
                        <span class="{{ $s->program === 'PPL' ? 'text-blue-700 font-medium' : 'text-outline' }}">
                            {{ $s->program === 'PPL' ? $s->kuota_ppl : '-' }}
                        </span>
                    </td>
                    <td class="px-md py-3">
                        <a target="_blank" class="text-[12px] text-secondary hover:underline flex items-center gap-1"
                           href="https://www.google.com/maps?q={{ $s->latitude }},{{ $s->longitude }}">
                            <i class="ti ti-map text-[14px]"></i>
                            {{ $s->latitude }}, {{ $s->longitude }}
                        </a>
                    </td>
                    <td class="px-md py-3">
                        <span class="text-[12px] px-2 py-1 rounded-full font-medium {{ $s->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-md py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.schools.edit', $s) }}"
                               class="text-[12px] text-primary hover:underline font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.schools.destroy', $s) }}" class="inline"
                                  onsubmit="return confirm('Hapus lokasi ini?');">
                                @csrf @method('DELETE')
                                <button class="text-[12px] text-error hover:underline font-medium">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-on-surface-variant">
                        <i class="ti ti-map-pin-off text-[48px] opacity-30 block mb-2"></i>
                        <p class="font-body-sm">Belum ada lokasi terdaftar.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-md">{{ $schools->links() }}</div>
@endsection
