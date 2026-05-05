@extends('layouts.app')
@section('title', 'Kelas Saya')
@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Daftar Kelas</h2>
        <p class="text-gray-500 mt-1">Kelola dan lihat aktivitas pembelajaran Anda.</p>
    </div>
</div>

@if(!$profile)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="bg-amber-100 p-3 rounded-lg flex-shrink-0">
                <i class="ti ti-alert-triangle text-amber-700 text-2xl"></i>
            </div>
            <div>
                <p class="font-semibold text-amber-900">Profil belum lengkap</p>
                <p class="text-sm text-amber-800 mt-1">Lengkapi profil dan tunggu persetujuan admin untuk mengakses kelas.</p>
            </div>
        </div>
        <a href="{{ route('mahasiswa.profile.create') }}"
           class="flex-shrink-0 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm px-6 py-2 rounded-lg transition-colors">
            Lengkapi Profil
        </a>
    </div>

@elseif(!$profile->isApproved())
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 flex items-start gap-4">
        <div class="bg-blue-100 p-3 rounded-lg flex-shrink-0">
            <i class="ti ti-hourglass text-blue-700 text-2xl"></i>
        </div>
        <div>
            <p class="font-semibold text-blue-900">Menunggu Persetujuan</p>
            <p class="text-sm text-blue-800 mt-1">Profil Anda masih dalam proses review admin. Kelas akan tersedia setelah disetujui.</p>
        </div>
    </div>

@elseif($classes->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-12 text-center text-on-surface-variant">
        <i class="ti ti-school text-6xl opacity-30 block mb-3"></i>
        <p class="text-base">Belum ada kelas yang tersedia.</p>
        <p class="text-sm mt-1">Kelas muncul setelah penempatan KPM/PPL Anda disetujui.</p>
    </div>

@else
    @if(isset($assignmentCount) && $assignmentCount > 0)
        <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex items-center gap-4 mb-6">
            <i class="ti ti-clipboard-list text-primary text-2xl"></i>
            <div>
                <p class="font-semibold text-primary text-sm">{{ $assignmentCount }} tugas tersedia</p>
                <p class="text-xs text-on-surface-variant">Buka detail kelas untuk melihat dan mengumpulkan tugas.</p>
            </div>
        </div>
    @endif

    @php
        $gradients = [
            'KPM' => ['from-amber-400 to-amber-600',   'bg-amber-800',   'ti-home'],
            'PPL' => ['from-blue-400 to-blue-700',     'bg-blue-800',    'ti-school'],
        ];
        $fallbacks = [
            ['from-teal-400 to-teal-600',    'bg-teal-800',   'ti-building'],
            ['from-purple-500 to-purple-700','bg-purple-900', 'ti-map-pin'],
            ['from-indigo-400 to-indigo-700','bg-indigo-800', 'ti-school'],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($classes as $reg)
            @php
                $grp = $gradients[$reg->program] ?? $fallbacks[$loop->index % count($fallbacks)];
                [$grad, $circleBg, $icon] = $grp;
                $initial = strtoupper(substr($reg->school->name, 0, 1));
                $programLabel = $reg->program === 'KPM' ? 'KPM — Desa' : 'PPL — Sekolah';
            @endphp

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow relative">

                {{-- Card Header --}}
                <div class="h-28 bg-gradient-to-r {{ $grad }} p-4 relative">
                    <h3 class="text-white font-bold text-lg leading-tight w-3/4 pr-2 line-clamp-2">{{ $reg->school->name }}</h3>
                    <p class="text-white/70 text-xs mt-1">{{ $programLabel }}</p>

                    {{-- Overlapping initial circle --}}
                    <div class="absolute -bottom-10 right-4 w-20 h-20 rounded-full {{ $circleBg }} flex items-center justify-center text-white text-3xl font-bold border-4 border-white shadow-lg">
                        {{ $initial }}
                    </div>

                    {{-- Decorative icon --}}
                    <div class="absolute top-0 right-0 w-16 h-full opacity-20 overflow-hidden text-white pointer-events-none">
                        <i class="ti {{ $icon }} text-6xl mt-4 ml-4 rotate-12 block"></i>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="h-32 p-4 pt-12">
                    @if($reg->gelombang)
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">{{ $reg->gelombang->label() }}</p>
                    @endif
                    @if($reg->school->supervisor)
                        <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-1">
                            <i class="ti ti-user-check text-xs text-secondary"></i>
                            {{ Str::limit($reg->school->supervisor->name, 28) }}
                        </p>
                    @endif
                    <p class="text-xs text-on-surface-variant flex items-center gap-1 mt-1">
                        <i class="ti ti-navigation text-xs"></i>
                        {{ number_format($reg->distance_km, 1) }} km dari domisili
                    </p>
                </div>

                {{-- Card Footer --}}
                <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-end gap-5 text-on-surface-variant">
                    <a href="{{ route('mahasiswa.class.assignments', $reg) }}"
                       title="Lihat Tugas"
                       class="hover:text-primary transition-colors">
                        <i class="ti ti-clipboard-list text-xl"></i>
                    </a>
                    <a href="{{ route('mahasiswa.class.assignments', $reg) }}"
                       title="Masuk Kelas"
                       class="hover:text-primary transition-colors">
                        <i class="ti ti-door-enter text-xl"></i>
                    </a>
                    <button class="hover:text-primary transition-colors">
                        <i class="ti ti-dots-vertical text-xl"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
