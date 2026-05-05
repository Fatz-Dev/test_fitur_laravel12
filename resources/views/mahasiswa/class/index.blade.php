@extends('layouts.app')
@section('title', 'SIPEP Class')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">SIPEP Class</h2>
        <p class="font-body-sm text-on-surface-variant">Kelas dan tugas program KPM &amp; PPL Anda</p>
    </div>
</div>

@if(!$profile)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-md">
        <div class="flex items-start gap-md">
            <div class="bg-amber-100 p-3 rounded-lg flex-shrink-0">
                <i class="ti ti-alert-triangle text-amber-700 text-[28px]"></i>
            </div>
            <div>
                <p class="font-label-md text-amber-900 text-sm">Profil belum lengkap</p>
                <p class="font-body-sm text-amber-800 mt-1">Lengkapi profil dan tunggu persetujuan admin untuk mengakses SIPEP Class.</p>
            </div>
        </div>
        <a href="{{ route('mahasiswa.profile.create') }}"
           class="flex-shrink-0 bg-amber-600 hover:bg-amber-700 text-white font-label-md text-sm px-lg py-2 rounded-lg transition-colors">
            Lengkapi Profil
        </a>
    </div>

@elseif(!$profile->isApproved())
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-xl flex items-start gap-md">
        <div class="bg-blue-100 p-3 rounded-lg flex-shrink-0">
            <i class="ti ti-hourglass text-blue-700 text-[28px]"></i>
        </div>
        <div>
            <p class="font-label-md text-blue-900 text-sm">Menunggu Persetujuan</p>
            <p class="font-body-sm text-blue-800 mt-1">Profil Anda masih dalam proses review admin. SIPEP Class akan tersedia setelah disetujui.</p>
        </div>
    </div>

@elseif($classes->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-xl text-center text-on-surface-variant">
        <i class="ti ti-school text-[56px] opacity-30 block mb-3"></i>
        <p class="font-body-md">Belum ada kelas yang tersedia.</p>
        <p class="font-body-sm text-on-surface-variant mt-1">Kelas muncul setelah penempatan KPM/PPL Anda disetujui.</p>
    </div>

@else
    @if(isset($assignmentCount) && $assignmentCount > 0)
        <div class="bg-primary/5 border border-primary/20 rounded-xl p-md flex items-center gap-md mb-lg">
            <i class="ti ti-clipboard-list text-primary text-[24px]"></i>
            <div>
                <p class="font-label-md text-primary text-sm">{{ $assignmentCount }} tugas tersedia</p>
                <p class="text-[12px] text-on-surface-variant">Buka detail kelas untuk melihat dan mengumpulkan tugas.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
        @foreach($classes as $reg)
            @php
                $programColor = $reg->program === 'KPM' ? 'amber' : 'blue';
                $programLabel = $reg->program === 'KPM' ? 'Desa' : 'Sekolah';
            @endphp
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="h-2 {{ $reg->program === 'KPM' ? 'bg-amber-400' : 'bg-blue-500' }}"></div>
                <div class="p-lg">
                    <div class="flex items-start justify-between mb-md">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                                    {{ $reg->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $reg->program }}
                                </span>
                                @if($reg->gelombang)
                                    <span class="text-[11px] bg-primary/10 text-primary px-2 py-0.5 rounded-full font-medium">
                                        {{ $reg->gelombang->label() }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-label-md text-on-surface text-[16px] leading-snug">
                                {{ $reg->program }} {{ $programLabel }} {{ $reg->school->name }}
                            </h3>
                        </div>
                        <div class="h-10 w-10 rounded-xl {{ $reg->program === 'KPM' ? 'bg-amber-50' : 'bg-blue-50' }} flex items-center justify-center flex-shrink-0 ml-2">
                            <i class="ti {{ $reg->program === 'KPM' ? 'ti-home' : 'ti-school' }} text-[20px] {{ $reg->program === 'KPM' ? 'text-amber-600' : 'text-blue-600' }}"></i>
                        </div>
                    </div>

                    <p class="text-[12px] text-on-surface-variant mb-md flex items-center gap-1">
                        <i class="ti ti-map-pin text-[13px]"></i>
                        {{ Str::limit($reg->school->address, 70) }}
                    </p>

                    @if($reg->school->supervisor)
                        <div class="flex items-center gap-2 mb-md text-[12px] text-on-surface-variant">
                            <div class="h-5 w-5 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-[10px]">
                                {{ strtoupper(substr($reg->school->supervisor->name, 0, 1)) }}
                            </div>
                            <span>Supervisor: <span class="font-medium text-on-surface">{{ $reg->school->supervisor->name }}</span></span>
                        </div>
                    @endif

                    <div class="flex items-center gap-1 mb-lg text-[13px] text-on-surface-variant">
                        <i class="ti ti-navigation text-[14px]"></i>
                        <span>{{ number_format($reg->distance_km, 1) }} km dari domisili</span>
                    </div>

                    <a href="{{ route('mahasiswa.class.assignments', $reg) }}"
                       class="flex items-center justify-center gap-2 w-full py-2 bg-primary text-white rounded-lg text-sm font-label-md hover:bg-primary-container transition-colors">
                        <i class="ti ti-door-enter text-[16px]"></i>
                        Lihat Tugas
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
