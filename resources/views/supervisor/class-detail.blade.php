@extends('layouts.app')
@section('title', 'Kelas ' . $school->name)
@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-xs text-slate-500 mb-6">
    <a href="{{ route('supervisor.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
    <i class="ti ti-chevron-right text-xs"></i>
    <a href="{{ route('supervisor.classes.index') }}" class="hover:text-primary transition-colors">Daftar Kelas</a>
    <i class="ti ti-chevron-right text-xs"></i>
    <span class="text-on-surface font-semibold">{{ $school->name }}</span>
</nav>

{{-- School Info Card --}}
<div class="bg-white rounded-xl p-6 shadow-sm border border-blue-800 relative overflow-hidden mb-6">
    <div class="absolute top-0 right-0 w-48 h-48 bg-primary/5 rounded-bl-full -mr-16 -mt-16 pointer-events-none"></div>
    <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            @if($school->program === 'KPM')
                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold mb-3">KPM</span>
            @elseif($school->program === 'PPL')
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold mb-3">PPL</span>
            @else
                <span class="inline-block px-3 py-1 bg-secondary-container text-on-secondary-container rounded-full text-xs font-bold mb-3">KPM &amp; PPL</span>
            @endif

            <h1 class="text-3xl font-bold text-primary">{{ $school->name }}</h1>
            <div class="flex items-center gap-2 text-on-surface-variant text-sm mt-2">
                <i class="ti ti-map-pin text-secondary text-base"></i>
                <span>{{ $school->address }}</span>
            </div>
            @if($school->jenjang)
                <div class="flex items-center gap-2 text-on-surface-variant text-sm mt-1">
                    <i class="ti ti-building text-base text-on-surface-variant"></i>
                    <span>{{ $school->jenjang }}</span>
                </div>
            @endif
        </div>
        <div class="flex gap-4 shrink-0">
            <div class="bg-surface-container-low p-4 rounded-lg border border-surface-variant text-center min-w-[90px]">
                <p class="text-xs text-outline mb-1 font-medium">Total Tugas</p>
                <p class="text-2xl font-bold text-secondary">{{ $assignments->count() }}</p>
            </div>
            <div class="bg-surface-container-low p-4 rounded-lg border border-surface-variant text-center min-w-[90px]">
                <p class="text-xs text-outline mb-1 font-medium">Mahasiswa</p>
                <p class="text-2xl font-bold text-primary">{{ $registrations->count() }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Student List --}}
<div class="bg-white rounded-xl shadow-sm border border-blue-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-blue-800 flex items-center justify-between bg-slate-50/50">
        <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
            <i class="ti ti-users text-primary text-lg"></i>
            Daftar Mahasiswa
        </h2>
        <span class="text-xs text-blue-800 font-medium">{{ $registrations->count() }} mahasiswa terdaftar</span>
    </div>

    @if($registrations->isEmpty())
        <div class="p-12 text-center text-on-surface-variant">
            <i class="ti ti-users text-6xl opacity-30 block mb-3"></i>
            <p class="text-sm">Belum ada mahasiswa yang ditempatkan di lokasi ini.</p>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @php
                $avatarColors = [
                    'bg-primary/10 text-primary',
                    'bg-secondary-container text-on-secondary-container',
                    'bg-amber-100 text-amber-700',
                    'bg-indigo-100 text-indigo-700',
                    'bg-rose-100 text-rose-700',
                    'bg-teal-100 text-teal-700',
                ];
            @endphp

            @foreach($registrations as $reg)
                @php
                    $mhsProfile  = $reg->mahasiswaProfile;
                    $initials    = strtoupper(substr($mhsProfile->user->name, 0, 2));
                    $colorClass  = $avatarColors[$loop->index % count($avatarColors)];
                    $submitted   = \App\Models\Submission::where('mahasiswa_profile_id', $mhsProfile->id)->whereNotNull('submitted_at')->count();
                    $graded      = \App\Models\Submission::where('mahasiswa_profile_id', $mhsProfile->id)->whereNotNull('grade')->count();
                @endphp

                <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors group">
                    {{-- Avatar + info --}}
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full {{ $colorClass }} flex items-center justify-center font-bold text-lg border-2 border-white shadow-sm shrink-0">
                            {{ $initials }}
                        </div>
                        <div>
                            <h4 class="font-bold text-on-surface group-hover:text-primary transition-colors">{{ $mhsProfile->user->name }}</h4>
                            <p class="text-xs text-outline">NIM: {{ $mhsProfile->nim }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full
                                    {{ $reg->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $reg->program }}
                                </span>
                                @if($reg->gelombang)
                                    <span class="text-xs text-outline">{{ $reg->gelombang->label() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stats + action --}}
                    <div class="flex items-center gap-6">
                        <div class="hidden md:block text-right">
                            <p class="text-xs text-outline font-medium mb-0.5">Pengumpulan</p>
                            <p class="text-sm font-bold">
                                <span class="text-secondary">{{ $submitted }}</span>
                                <span class="text-on-surface-variant font-normal">/{{ $assignments->count() }}</span>
                            </p>
                            @if($graded > 0)
                                <p class="text-xs text-emerald-600 font-medium">{{ $graded }} dinilai</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('supervisor.students.assignments', [$school, $mhsProfile]) }}"
                               class="px-5 py-2 bg-white border border-primary text-primary rounded-lg text-sm font-bold hover:bg-primary hover:text-white transition-all active:scale-95 whitespace-nowrap">
                                Lihat
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination note --}}
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/30">
            <p class="text-xs text-outline">Menampilkan {{ $registrations->count() }} mahasiswa</p>
        </div>
    @endif
</div>
@endsection
