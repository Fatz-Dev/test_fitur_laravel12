@extends('layouts.app')
@section('title', 'Kelas ' . $school->name)
@section('content')

<div class="mb-lg">
    <a href="{{ route('supervisor.dashboard') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors mb-md">
        <i class="ti ti-arrow-left text-[16px]"></i> Kembali ke Dashboard
    </a>
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[12px] font-bold px-2 py-0.5 rounded-full
                    {{ $school->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ $school->program }}
                </span>
            </div>
            <h2 class="font-h2 text-h2 text-primary">{{ $school->name }}</h2>
            <p class="font-body-sm text-on-surface-variant flex items-center gap-1 mt-1">
                <i class="ti ti-map-pin text-[15px]"></i>
                {{ $school->address }}
            </p>
        </div>
        <div class="text-right hidden md:block">
            <p class="text-label-sm text-on-surface-variant">Total Tugas</p>
            <p class="font-h2 text-h2 text-secondary">{{ $assignments->count() }}</p>
        </div>
    </div>
</div>

{{-- Tugas tersedia --}}
<div class="bg-surface-container-low border border-surface-variant rounded-xl p-lg mb-lg">
    <h3 class="font-label-md text-on-surface mb-md flex items-center gap-2">
        <i class="ti ti-clipboard-list text-primary text-[18px]"></i>
        Daftar Tugas Aktif
    </h3>
    @if($assignments->isEmpty())
        <p class="text-sm text-on-surface-variant italic">Belum ada tugas dari admin.</p>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach($assignments as $a)
                <span class="inline-flex items-center gap-1 text-[12px] px-3 py-1 rounded-full
                    {{ $a->isPastDeadline() ? 'bg-error/10 text-error' : 'bg-emerald-100 text-emerald-700' }} font-medium">
                    <i class="ti ti-clock text-[12px]"></i>
                    {{ $a->title }} — {{ $a->deadline->format('d M Y') }}
                </span>
            @endforeach
        </div>
    @endif
</div>

{{-- Daftar mahasiswa --}}
<div class="flex items-center justify-between mb-md">
    <h3 class="font-h3 text-h3 text-on-surface">Daftar Mahasiswa</h3>
    <span class="text-[13px] text-on-surface-variant">{{ $registrations->count() }} mahasiswa</span>
</div>

@if($registrations->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-xl text-center text-on-surface-variant">
        <i class="ti ti-users text-[48px] opacity-30 block mb-2"></i>
        <p class="font-body-sm">Belum ada mahasiswa yang ditempatkan di lokasi ini.</p>
    </div>
@else
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                    <tr>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">NIM</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Program</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Gelombang</th>
                        <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider text-center">Pengumpulan</th>
                        <th class="px-md py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($registrations as $reg)
                    @php
                        $profile = $reg->mahasiswaProfile;
                        $submittedCount = \App\Models\Submission::where('mahasiswa_profile_id', $profile->id)
                            ->whereNotNull('submitted_at')
                            ->count();
                        $gradedCount = \App\Models\Submission::where('mahasiswa_profile_id', $profile->id)
                            ->whereNotNull('grade')
                            ->count();
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-md py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($profile->user->name, 0, 1)) }}
                                </div>
                                <span class="font-label-md text-on-surface">{{ $profile->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-md py-3 text-[13px] text-on-surface-variant">{{ $profile->nim }}</td>
                        <td class="px-md py-3">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                                {{ $reg->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $reg->program }}
                            </span>
                        </td>
                        <td class="px-md py-3 text-[12px] text-on-surface-variant">
                            {{ $reg->gelombang?->label() ?? '—' }}
                        </td>
                        <td class="px-md py-3 text-center">
                            <span class="text-[12px]">
                                <span class="font-label-md text-secondary">{{ $submittedCount }}</span>
                                <span class="text-on-surface-variant">/{{ $assignments->count() }} dikumpul</span>
                            </span>
                            @if($gradedCount > 0)
                                <span class="block text-[11px] text-emerald-600 mt-0.5">{{ $gradedCount }} dinilai</span>
                            @endif
                        </td>
                        <td class="px-md py-3 text-right">
                            <a href="{{ route('supervisor.students.assignments', [$school, $profile]) }}"
                               class="inline-flex items-center gap-1 text-[12px] text-primary hover:underline font-medium">
                                Lihat Tugas
                                <i class="ti ti-arrow-right text-[14px]"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
