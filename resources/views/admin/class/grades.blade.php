@extends('layouts.app')
@section('title', 'Nilai SIPEP Class')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Nilai SIPEP Class</h2>
        <p class="font-body-sm text-on-surface-variant">Rekap nilai mahasiswa berdasarkan program dan gelombang</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="flex flex-wrap gap-3 mb-lg">
    <select name="program" onchange="this.form.submit()"
            class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30">
        @foreach(['KPM','PPL'] as $p)
            <option value="{{ $p }}" @selected($program === $p)>{{ $p }}</option>
        @endforeach
    </select>
    <select name="gelombang_id" onchange="this.form.submit()"
            class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30">
        <option value="">Semua Gelombang</option>
        @foreach($gelombangList as $g)
            <option value="{{ $g->id }}" @selected($gelombangId == $g->id)>{{ $g->label() }}</option>
        @endforeach
    </select>
</form>

@if($assignments->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-xl text-center text-on-surface-variant">
        <i class="ti ti-clipboard-list text-[48px] opacity-30 block mb-2"></i>
        <p class="font-body-sm">Belum ada tugas yang dibuat. Tambahkan tugas terlebih dahulu.</p>
    </div>
@else
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                <tr>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Mahasiswa</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Lokasi</th>
                    @foreach($assignments as $a)
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider text-center min-w-[120px]">
                            <span class="block truncate max-w-[120px]" title="{{ $a->title }}">{{ Str::limit($a->title, 18) }}</span>
                            <span class="block text-[10px] font-normal text-outline mt-0.5">{{ $a->deadline->format('d/m/Y') }}</span>
                        </th>
                    @endforeach
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider text-center">Rata-rata</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($profiles as $profile)
                @php
                    $reg = $profile->registrations->firstWhere('program', $program);
                    $allGrades = [];
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-md py-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-7 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($profile->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-label-md text-on-surface">{{ $profile->user->name }}</p>
                                <p class="text-[11px] text-on-surface-variant">{{ $profile->nim }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-md py-3 text-[12px] text-on-surface-variant">
                        {{ $reg?->school?->name ?? '—' }}
                    </td>
                    @foreach($assignments as $a)
                        @php
                            $sub = $profile->submissions()->where('assignment_id', $a->id)->first();
                            if ($sub && $sub->grade !== null) $allGrades[] = $sub->grade;
                        @endphp
                        <td class="px-md py-3 text-center">
                            @if($sub && $sub->grade !== null)
                                <span class="inline-block w-10 text-center font-label-md text-secondary">{{ $sub->grade }}</span>
                            @elseif($sub && $sub->submitted_at)
                                <span class="text-[11px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Dikumpul</span>
                            @else
                                <span class="text-[11px] text-slate-400">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-md py-3 text-center">
                        @if(count($allGrades) > 0)
                            <span class="font-label-md text-primary">{{ number_format(array_sum($allGrades)/count($allGrades), 1) }}</span>
                        @else
                            <span class="text-slate-400 text-[12px]">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 3 + $assignments->count() }}" class="text-center py-12 text-on-surface-variant">
                        <i class="ti ti-users text-[48px] opacity-30 block mb-2"></i>
                        <p class="font-body-sm">Belum ada mahasiswa {{ $program }} yang disetujui.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-md">{{ $profiles->links() }}</div>
@endif
@endsection
