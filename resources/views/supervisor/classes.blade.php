@extends('layouts.app')
@section('title', 'Daftar Kelas')
@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Daftar Kelas</h2>
        <p class="text-gray-500 mt-1">Sekolah dan lokasi yang Anda supervisi.</p>
    </div>
    <a href="{{ route('supervisor.dashboard') }}"
       class="flex items-center gap-2 px-5 py-2.5 border border-slate-200 bg-white rounded-lg text-sm font-semibold text-on-surface-variant hover:bg-slate-50 transition-all">
        <i class="ti ti-layout-dashboard text-[18px]"></i>
        Dashboard
    </a>
</div>

@if($schools->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-xl text-center text-on-surface-variant">
        <i class="ti ti-school text-[56px] opacity-30 block mb-3"></i>
        <p class="font-body-md">Belum ada kelas yang ditugaskan kepada Anda.</p>
        <p class="font-body-sm mt-1">Hubungi administrator untuk penugasan sekolah.</p>
    </div>
@else
    {{-- Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @php
            $gradients = [
                ['from-blue-400 to-blue-700',   'bg-blue-800',   'bg-blue-100 text-blue-700',  'ti-school'],
                ['from-teal-400 to-teal-600',   'bg-teal-800',   'bg-teal-100 text-teal-700',  'ti-home'],
                ['from-purple-500 to-purple-700','bg-purple-900', 'bg-purple-100 text-purple-700','ti-building'],
                ['from-indigo-400 to-indigo-700','bg-indigo-800', 'bg-indigo-100 text-indigo-700','ti-map-pin'],
                ['from-rose-400 to-rose-700',   'bg-rose-900',   'bg-rose-100 text-rose-700',  'ti-school'],
                ['from-amber-400 to-amber-600', 'bg-amber-800',  'bg-amber-100 text-amber-700','ti-home'],
            ];
        @endphp

        @foreach($schools as $school)
            @php
                $idx  = $loop->index % count($gradients);
                [$grad, $circleBg, $badge, $icon] = $gradients[$idx];
                $initial = strtoupper(substr($school->name, 0, 1));
                $programLabel = $school->program ?? 'KPM & PPL';
            @endphp

            <a href="{{ route('supervisor.classes.show', $school) }}"
               class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow relative block">

                {{-- Card Header --}}
                <div class="h-28 bg-gradient-to-r {{ $grad }} p-4 relative">
                    <h3 class="text-white font-bold text-lg leading-tight w-3/4 pr-2 line-clamp-2">{{ $school->name }}</h3>
                    <p class="text-white/70 text-xs mt-1">{{ $programLabel }}</p>

                    {{-- Overlapping circle avatar --}}
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
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Mahasiswa Aktif</p>
                    <p class="text-2xl font-bold text-on-surface">{{ $school->mahasiswa_count ?? 0 }}
                        <span class="text-sm font-normal text-on-surface-variant">mahasiswa</span>
                    </p>
                    @if($school->jenjang)
                        <p class="text-xs text-on-surface-variant mt-1 flex items-center gap-1">
                            <i class="ti ti-building text-[13px]"></i>{{ $school->jenjang }}
                        </p>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-on-surface-variant">
                    <span class="flex items-center gap-1 text-xs">
                        <i class="ti ti-map-pin text-[13px] text-secondary"></i>
                        <span class="truncate max-w-[130px]">{{ Str::limit($school->address, 30) }}</span>
                    </span>
                    <span class="flex items-center gap-1 text-xs font-semibold text-primary">
                        Buka <i class="ti ti-arrow-right text-[13px]"></i>
                    </span>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
