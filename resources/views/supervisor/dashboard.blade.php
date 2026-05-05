@extends('layouts.app')
@section('title', 'Dashboard Supervisor')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Dashboard Supervisor</h2>
        <p class="font-body-sm text-on-surface-variant">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
</div>

{{-- Stat cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-lg">
    <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm flex items-center gap-md">
        <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-building-school text-primary text-[24px]"></i>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Lokasi Ditangani</p>
            <p class="font-h2 text-h2 text-primary">{{ $schools->count() }}</p>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm flex items-center gap-md">
        <div class="h-12 w-12 rounded-xl bg-secondary/10 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-users text-secondary text-[24px]"></i>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Total Mahasiswa</p>
            <p class="font-h2 text-h2 text-secondary">{{ $totalMahasiswa }}</p>
        </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm flex items-center gap-md">
        <div class="h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-clipboard-check text-amber-600 text-[24px]"></i>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Menunggu Penilaian</p>
            <p class="font-h2 text-h2 text-amber-600">{{ $pendingGrades }}</p>
        </div>
    </div>
</div>

{{-- Kelas --}}
<h3 class="font-h3 text-h3 text-on-surface mb-md">Kelas Saya</h3>

@if($schools->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-xl text-center text-on-surface-variant">
        <i class="ti ti-building-off text-[56px] opacity-30 block mb-3"></i>
        <p class="font-body-md">Anda belum ditugaskan ke lokasi manapun.</p>
        <p class="font-body-sm text-on-surface-variant mt-1">Hubungi admin untuk mendapatkan penugasan.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
        @foreach($schools as $school)
            @php
                $mahasiswaCount = $school->registrations->where('status', 'approved')->count();
                $programLabel = $school->program === 'KPM' ? 'Desa' : 'Sekolah';
                $programColor = $school->program === 'KPM' ? 'amber' : 'blue';
            @endphp
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="h-2 {{ $school->program === 'KPM' ? 'bg-amber-400' : 'bg-blue-500' }}"></div>
                <div class="p-lg">
                    <div class="flex items-start justify-between mb-md">
                        <div>
                            <span class="inline-block text-[11px] font-bold px-2 py-0.5 rounded-full mb-2
                                {{ $school->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $school->program }} &bull; {{ $programLabel }}
                            </span>
                            <h4 class="font-label-md text-on-surface text-[16px] leading-snug">{{ $school->name }}</h4>
                        </div>
                        <div class="h-10 w-10 rounded-xl {{ $school->program === 'KPM' ? 'bg-amber-50' : 'bg-blue-50' }} flex items-center justify-center flex-shrink-0 ml-2">
                            <i class="ti {{ $school->program === 'KPM' ? 'ti-home' : 'ti-school' }} text-[20px] {{ $school->program === 'KPM' ? 'text-amber-600' : 'text-blue-600' }}"></i>
                        </div>
                    </div>

                    <p class="text-[12px] text-on-surface-variant mb-md flex items-center gap-1">
                        <i class="ti ti-map-pin text-[13px]"></i>
                        {{ Str::limit($school->address, 60) }}
                    </p>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1 text-[13px] text-on-surface-variant">
                            <i class="ti ti-users text-[15px]"></i>
                            <span><span class="font-label-md text-on-surface">{{ $mahasiswaCount }}</span> mahasiswa</span>
                        </div>
                        <div class="flex items-center gap-1 text-[13px] text-on-surface-variant">
                            <i class="ti ti-clipboard-list text-[15px]"></i>
                            <span><span class="font-label-md text-on-surface">{{ $assignmentCount }}</span> tugas</span>
                        </div>
                    </div>

                    <a href="{{ route('supervisor.classes.show', $school) }}"
                       class="mt-md flex items-center justify-center gap-2 w-full py-2 bg-primary text-white rounded-lg text-sm font-label-md hover:bg-primary-container transition-colors">
                        <i class="ti ti-door-enter text-[16px]"></i>
                        Masuk Kelas
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
