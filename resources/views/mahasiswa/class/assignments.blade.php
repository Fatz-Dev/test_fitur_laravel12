@extends('layouts.app')
@section('title', 'Tugas Kelas')
@section('content')

<div class="mb-lg">
    <a href="{{ route('mahasiswa.class.index') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors mb-md">
        <i class="ti ti-arrow-left text-[16px]"></i> Kembali ke SIPEP Class
    </a>

    <div class="flex items-start gap-md">
        <div class="h-12 w-12 rounded-xl {{ $registration->program === 'KPM' ? 'bg-amber-50' : 'bg-blue-50' }} flex items-center justify-center flex-shrink-0">
            <i class="ti {{ $registration->program === 'KPM' ? 'ti-home' : 'ti-school' }} text-[22px] {{ $registration->program === 'KPM' ? 'text-amber-600' : 'text-blue-600' }}"></i>
        </div>
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                    {{ $registration->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ $registration->program }}
                </span>
            </div>
            <h2 class="font-h2 text-h2 text-primary">{{ $registration->school->name }}</h2>
            <p class="font-body-sm text-on-surface-variant flex items-center gap-1">
                <i class="ti ti-map-pin text-[14px]"></i>
                {{ $registration->school->address }}
            </p>
        </div>
    </div>
</div>

@if($assignments->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-xl text-center text-on-surface-variant">
        <i class="ti ti-clipboard-list text-[56px] opacity-30 block mb-3"></i>
        <p class="font-body-md">Belum ada tugas dari admin.</p>
        <p class="font-body-sm mt-1">Pantau halaman ini secara berkala.</p>
    </div>
@else
    {{-- Summary bar --}}
    @php
        $submittedCount = collect($assignments)->filter(fn($a) => isset($submissions[$a->id]) && $submissions[$a->id]->submitted_at)->count();
        $gradedCount = collect($assignments)->filter(fn($a) => isset($submissions[$a->id]) && $submissions[$a->id]->grade !== null)->count();
    @endphp
    <div class="grid grid-cols-3 gap-gutter mb-lg">
        <div class="bg-white border border-slate-200 rounded-xl p-md text-center shadow-sm">
            <p class="font-h2 text-h2 text-primary">{{ $assignments->count() }}</p>
            <p class="text-[12px] text-on-surface-variant mt-1">Total Tugas</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-md text-center shadow-sm">
            <p class="font-h2 text-h2 text-secondary">{{ $submittedCount }}</p>
            <p class="text-[12px] text-on-surface-variant mt-1">Dikumpulkan</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-md text-center shadow-sm">
            <p class="font-h2 text-h2 text-amber-600">{{ $gradedCount }}</p>
            <p class="text-[12px] text-on-surface-variant mt-1">Sudah Dinilai</p>
        </div>
    </div>

    <div class="space-y-md">
        @foreach($assignments as $a)
            @php $sub = $submissions[$a->id] ?? null; @endphp
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-start gap-md p-lg">
                    {{-- Status icon --}}
                    <div class="flex-shrink-0 mt-1">
                        @if($sub && $sub->grade !== null)
                            <div class="h-10 w-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                                <i class="ti ti-star-filled text-emerald-600 text-[20px]"></i>
                            </div>
                        @elseif($sub && $sub->submitted_at)
                            <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="ti ti-circle-check text-blue-600 text-[20px]"></i>
                            </div>
                        @elseif($a->isPastDeadline())
                            <div class="h-10 w-10 rounded-xl bg-error/10 flex items-center justify-center">
                                <i class="ti ti-clock-x text-error text-[20px]"></i>
                            </div>
                        @else
                            <div class="h-10 w-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i class="ti ti-clock text-amber-600 text-[20px]"></i>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-label-md text-on-surface text-[15px]">{{ $a->title }}</h3>
                                @if($a->description)
                                    <p class="text-[13px] text-on-surface-variant mt-0.5 line-clamp-2">{{ $a->description }}</p>
                                @endif
                            </div>
                            @if($sub && $sub->grade !== null)
                                <div class="flex-shrink-0 bg-emerald-50 border border-emerald-200 rounded-xl px-md py-sm text-center">
                                    <p class="text-[10px] text-emerald-600 font-medium">NILAI</p>
                                    <p class="font-h2 text-emerald-700" style="font-size:22px">{{ $sub->grade }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[12px]">
                            <span class="flex items-center gap-1 {{ $a->isPastDeadline() ? 'text-error' : 'text-on-surface-variant' }}">
                                <i class="ti ti-clock text-[13px]"></i>
                                Tenggat: {{ $a->deadline->format('d M Y H:i') }}
                                @if($a->isPastDeadline()) <span class="font-medium">(Lewat)</span> @endif
                            </span>
                            @if($sub && $sub->submitted_at)
                                <span class="flex items-center gap-1 text-secondary">
                                    <i class="ti ti-check text-[13px]"></i>
                                    Dikumpul {{ $sub->submitted_at->format('d M Y H:i') }}
                                </span>
                            @endif
                        </div>

                        @if($sub && $sub->comment)
                            <div class="mt-md bg-surface-container-low border border-surface-variant rounded-lg p-sm text-[12px]">
                                <span class="font-label-md text-on-surface-variant">Feedback: </span>
                                <span class="text-on-surface">{{ $sub->comment }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-100 px-lg py-md flex justify-end bg-surface-container-low/50">
                    <a href="{{ route('mahasiswa.class.submission', [$registration, $a]) }}"
                       class="inline-flex items-center gap-1 text-[12px] font-label-md
                           {{ ($sub && $sub->submitted_at) ? 'text-on-surface-variant border border-slate-200 hover:bg-slate-100' : 'bg-primary text-white hover:bg-primary-container' }}
                           px-md py-1.5 rounded-lg transition-colors">
                        @if($sub && $sub->submitted_at)
                            <i class="ti ti-eye text-[14px]"></i>
                            Lihat Pengumpulan
                        @else
                            <i class="ti ti-upload text-[14px]"></i>
                            Kumpulkan Tugas
                        @endif
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
