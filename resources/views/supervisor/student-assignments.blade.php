@extends('layouts.app')
@section('title', 'Tugas ' . $profile->user->name)
@section('content')

<div class="mb-lg">
    <a href="{{ route('supervisor.classes.show', $school) }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors mb-md">
        <i class="ti ti-arrow-left text-[16px]"></i> Kembali ke Kelas
    </a>

    <div class="flex items-start gap-md">
        <div class="h-14 w-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl flex-shrink-0">
            {{ strtoupper(substr($profile->user->name, 0, 1)) }}
        </div>
        <div>
            <h2 class="font-h2 text-h2 text-primary">{{ $profile->user->name }}</h2>
            <p class="font-body-sm text-on-surface-variant">NIM: {{ $profile->nim }} &bull; {{ $reg->program }} &bull; {{ $school->name }}</p>
        </div>
    </div>
</div>

@if($assignments->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-xl text-center text-on-surface-variant">
        <i class="ti ti-clipboard-list text-[48px] opacity-30 block mb-2"></i>
        <p class="font-body-sm">Belum ada tugas yang dibuat admin.</p>
    </div>
@else
    <div class="space-y-md">
        @foreach($assignments as $a)
            @php $sub = $submissions[$a->id] ?? null; @endphp
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-start justify-between p-lg">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-label-md text-on-surface text-[15px]">{{ $a->title }}</h3>
                            @if($a->isPastDeadline())
                                <span class="text-[11px] bg-error/10 text-error px-2 py-0.5 rounded-full font-medium">Lewat Tenggat</span>
                            @else
                                <span class="text-[11px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Aktif</span>
                            @endif
                        </div>
                        @if($a->description)
                            <p class="text-[13px] text-on-surface-variant mb-2">{{ $a->description }}</p>
                        @endif
                        <div class="flex items-center gap-4 text-[12px] text-on-surface-variant">
                            <span class="flex items-center gap-1">
                                <i class="ti ti-clock text-[13px]"></i>
                                Tenggat: {{ $a->deadline->format('d M Y H:i') }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-md flex-shrink-0 text-right">
                        @if($sub && $sub->grade !== null)
                            <div class="inline-flex flex-col items-center bg-emerald-50 border border-emerald-200 rounded-xl px-md py-sm">
                                <span class="text-[11px] text-emerald-600 font-medium">Nilai</span>
                                <span class="font-h2 text-h2 text-emerald-700">{{ $sub->grade }}</span>
                                <span class="text-[10px] text-emerald-600">/100</span>
                            </div>
                        @elseif($sub && $sub->submitted_at)
                            <span class="inline-block text-[11px] bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-medium">
                                Dikumpulkan
                            </span>
                        @else
                            <span class="inline-block text-[11px] bg-slate-100 text-slate-500 px-3 py-1 rounded-full font-medium">
                                Belum kumpul
                            </span>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-100 px-lg py-md flex items-center justify-between bg-surface-container-low/50">
                    @if($sub && $sub->submitted_at)
                        <div class="flex items-center gap-1 text-[12px] text-on-surface-variant">
                            <i class="ti ti-clock-check text-[13px] text-secondary"></i>
                            Dikumpul: {{ $sub->submitted_at->format('d M Y H:i') }}
                        </div>
                        <a href="{{ route('supervisor.submissions.show', [$school, $profile, $a]) }}"
                           class="inline-flex items-center gap-1 text-[12px] bg-primary text-white px-md py-1.5 rounded-lg hover:bg-primary-container transition-colors font-label-md">
                            <i class="ti ti-eye text-[14px]"></i>
                            {{ $sub->grade !== null ? 'Lihat & Edit Nilai' : 'Nilai Tugas' }}
                        </a>
                    @else
                        <span class="text-[12px] text-on-surface-variant italic">Mahasiswa belum mengumpulkan</span>
                        <a href="{{ route('supervisor.submissions.show', [$school, $profile, $a]) }}"
                           class="inline-flex items-center gap-1 text-[12px] text-on-surface-variant hover:text-primary px-md py-1.5 rounded-lg hover:bg-slate-100 transition-colors font-label-md border border-slate-200">
                            <i class="ti ti-eye text-[14px]"></i>
                            Lihat Detail
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
