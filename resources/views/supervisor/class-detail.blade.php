@extends('layouts.app')
@section('title', 'Kelas ' . $school->name)
@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-label-sm text-outline mb-lg">
    <a href="{{ route('supervisor.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
    <i class="ti ti-chevron-right text-[14px]"></i>
    <span class="text-on-surface font-semibold">{{ $school->name }}</span>
</nav>

{{-- School Header Card --}}
<div class="bg-white rounded-xl p-lg shadow-sm border border-slate-200 relative overflow-hidden mb-lg">
    <div class="absolute top-0 right-0 w-48 h-48 bg-primary/5 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
    <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-md">
        <div>
            @if($school->program === 'KPM')
                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-label-sm font-bold mb-sm">KPM</span>
            @elseif($school->program === 'PPL')
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-label-sm font-bold mb-sm">PPL</span>
            @else
                <span class="inline-block px-3 py-1 bg-secondary-container text-on-secondary-container rounded-full text-label-sm font-bold mb-sm">KPM &amp; PPL</span>
            @endif
            <h1 class="font-h2 text-h2 text-primary">{{ $school->name }}</h1>
            <div class="flex items-center gap-2 text-on-surface-variant font-body-sm mt-1">
                <i class="ti ti-map-pin text-[16px] text-secondary"></i>
                <span>{{ $school->address }}</span>
            </div>
        </div>
        <div class="flex gap-md shrink-0">
            <div class="bg-surface-container-low p-md rounded-lg border border-surface-variant text-center min-w-[90px]">
                <p class="text-label-sm text-outline mb-1">Total Tugas</p>
                <p class="font-h2 text-h2 text-secondary">{{ $assignments->count() }}</p>
            </div>
            <div class="bg-surface-container-low p-md rounded-lg border border-surface-variant text-center min-w-[90px]">
                <p class="text-label-sm text-outline mb-1">Mahasiswa</p>
                <p class="font-h2 text-h2 text-primary">{{ $registrations->count() }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Daftar Mahasiswa --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-lg py-md border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <h2 class="font-h3 text-h3 text-on-surface flex items-center gap-2">
            <i class="ti ti-users text-primary text-[20px]"></i>
            Daftar Mahasiswa
        </h2>
        <span class="text-label-sm text-outline">{{ $registrations->count() }} mahasiswa</span>
    </div>

    @if($registrations->isEmpty())
        <div class="p-xl text-center text-on-surface-variant">
            <i class="ti ti-users text-[48px] opacity-30 block mb-2"></i>
            <p class="font-body-sm">Belum ada mahasiswa yang ditempatkan di lokasi ini.</p>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($registrations as $reg)
                @php
                    $profile = $reg->mahasiswaProfile;
                    $initials = strtoupper(substr($profile->user->name, 0, 2));
                    $submittedCount = \App\Models\Submission::where('mahasiswa_profile_id', $profile->id)->whereNotNull('submitted_at')->count();
                    $gradedCount = \App\Models\Submission::where('mahasiswa_profile_id', $profile->id)->whereNotNull('grade')->count();
                    $bgColors = ['bg-primary/10 text-primary', 'bg-secondary-container text-on-secondary-container', 'bg-amber-100 text-amber-700', 'bg-indigo-100 text-indigo-700'];
                    $bgColor = $bgColors[$loop->index % count($bgColors)];
                @endphp
                <div class="px-lg py-md flex items-center justify-between hover:bg-slate-50 transition-colors group">
                    <div class="flex items-center gap-md">
                        <div class="h-12 w-12 rounded-full {{ $bgColor }} flex items-center justify-center font-bold text-lg border-2 border-white shadow-sm flex-shrink-0">
                            {{ $initials }}
                        </div>
                        <div>
                            <h4 class="font-bold text-on-surface group-hover:text-primary transition-colors">{{ $profile->user->name }}</h4>
                            <p class="text-label-sm text-outline">NIM: {{ $profile->nim }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                                    {{ $reg->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $reg->program }}
                                </span>
                                @if($reg->gelombang)
                                    <span class="text-[11px] text-outline">{{ $reg->gelombang->label() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-md">
                        <div class="hidden md:block text-right">
                            <p class="text-label-sm text-outline">Pengumpulan</p>
                            <p class="text-[13px] font-bold">
                                <span class="text-secondary">{{ $submittedCount }}</span>
                                <span class="text-on-surface-variant font-normal">/{{ $assignments->count() }}</span>
                            </p>
                            @if($gradedCount > 0)
                                <p class="text-[11px] text-emerald-600">{{ $gradedCount }} dinilai</p>
                            @endif
                        </div>
                        <a href="{{ route('supervisor.students.assignments', [$school, $profile]) }}"
                           class="px-5 py-2 bg-white border border-primary text-primary rounded-lg text-label-md font-bold hover:bg-primary hover:text-white transition-all active:scale-95 whitespace-nowrap">
                            Lihat Tugas
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
