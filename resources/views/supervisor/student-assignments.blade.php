@extends('layouts.app')
@section('title', 'Tugas ' . $profile->user->name)
@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-label-sm text-outline mb-lg">
    <a href="{{ route('supervisor.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
    <i class="ti ti-chevron-right text-[14px]"></i>
    <a href="{{ route('supervisor.classes.show', $school) }}" class="hover:text-primary transition-colors">{{ $school->name }}</a>
    <i class="ti ti-chevron-right text-[14px]"></i>
    <span class="text-on-surface font-semibold">Daftar Tugas</span>
</nav>

<div class="grid grid-cols-1 md:grid-cols-12 gap-lg items-start">

    {{-- Left: Assignment list --}}
    <div class="md:col-span-8 space-y-lg">

        {{-- Student header --}}
        <div class="bg-white rounded-xl p-lg shadow-sm border border-slate-200 flex items-center gap-md">
            <div class="h-14 w-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr($profile->user->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="font-h2 text-h2 text-primary">{{ $profile->user->name }}</h2>
                <p class="font-body-sm text-on-surface-variant">NIM: {{ $profile->nim }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                        {{ $reg->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $reg->program }}
                    </span>
                    <span class="text-[12px] text-outline">{{ $school->name }}</span>
                </div>
            </div>
        </div>

        {{-- Filter bar --}}
        <div class="bg-surface-container-lowest p-md rounded-xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-md">
            <div class="flex items-center p-1 bg-slate-100 rounded-lg">
                <button onclick="filterTugas('all')" id="btn-all"
                        class="px-lg py-2 rounded-md bg-white shadow-sm text-primary font-label-md text-sm">Semua</button>
                <button onclick="filterTugas('submitted')" id="btn-submitted"
                        class="px-lg py-2 rounded-md text-slate-500 hover:text-primary font-label-md text-sm">Dikumpulkan</button>
                <button onclick="filterTugas('unsubmitted')" id="btn-unsubmitted"
                        class="px-lg py-2 rounded-md text-slate-500 hover:text-primary font-label-md text-sm">Belum Kumpul</button>
            </div>
            <span class="text-label-sm text-outline">{{ $assignments->count() }} tugas</span>
        </div>

        {{-- Assignment cards --}}
        @if($assignments->isEmpty())
            <div class="bg-surface-container-lowest p-xl text-center rounded-xl border border-slate-200 text-on-surface-variant">
                <i class="ti ti-clipboard-list text-[48px] opacity-30 block mb-2"></i>
                <p class="font-body-sm">Belum ada tugas yang dibuat admin.</p>
            </div>
        @else
            <div class="space-y-md" id="assignment-list">
                @foreach($assignments as $a)
                    @php
                        $sub = $submissions[$a->id] ?? null;
                        $status = 'unsubmitted';
                        if ($sub && $sub->grade !== null) $status = 'graded';
                        elseif ($sub && $sub->submitted_at) $status = 'submitted';
                    @endphp
                    <div class="bg-surface-container-lowest p-lg rounded-xl shadow-sm border border-slate-200 hover:border-primary/30 transition-all flex flex-col md:flex-row gap-lg items-start md:items-center assignment-card" data-status="{{ $status }}">
                        <div class="w-12 h-12 rounded-lg {{ $a->isPastDeadline() ? 'bg-error/10' : 'bg-blue-50' }} flex items-center justify-center {{ $a->isPastDeadline() ? 'text-error' : 'text-primary' }} shrink-0">
                            <i class="ti ti-clipboard-list text-[24px]"></i>
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-h3 text-h3 text-on-surface">{{ $a->title }}</h3>
                                @if($a->program)
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $a->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">{{ $a->program }}</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-md text-body-sm text-on-surface-variant">
                                @if($a->description)
                                    <span class="line-clamp-1">{{ $a->description }}</span>
                                @endif
                                <span class="flex items-center gap-1 {{ $a->isPastDeadline() ? 'text-error font-medium' : '' }}">
                                    <i class="ti ti-clock text-[15px]"></i>
                                    Tenggat: {{ $a->deadline->format('d M Y, H:i') }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-sm shrink-0">
                            @if($status === 'graded')
                                <div class="flex items-center gap-2">
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-label-sm font-bold">Nilai: {{ $sub->grade }}/100</span>
                                </div>
                            @elseif($status === 'submitted')
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-label-sm font-bold">Dikumpulkan</span>
                            @else
                                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-label-sm">Belum Kumpul</span>
                            @endif
                            <a href="{{ route('supervisor.submissions.show', [$school, $profile, $a]) }}"
                               class="flex items-center justify-center gap-2 bg-secondary text-on-secondary px-lg py-2 rounded-lg font-label-md text-sm hover:opacity-90 transition-all w-full md:w-auto">
                                {{ $status === 'submitted' ? 'Beri Nilai' : ($status === 'graded' ? 'Lihat & Edit' : 'Lihat Detail') }}
                                <i class="ti ti-arrow-right text-[14px]"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Right: Summary card --}}
    <div class="md:col-span-4 space-y-lg">
        <div class="bg-primary text-on-primary p-lg rounded-xl shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-h3 text-h3 mb-2">Ringkasan Tugas</h3>
                @php
                    $totalA = $assignments->count();
                    $submittedA = collect($submissions)->filter(fn($s) => $s->submitted_at)->count();
                    $gradedA = collect($submissions)->filter(fn($s) => $s->grade !== null)->count();
                    $pct = $totalA > 0 ? round($submittedA / $totalA * 100) : 0;
                @endphp
                <div class="space-y-md mt-lg">
                    <div class="flex justify-between text-body-sm mb-1">
                        <span>Progress Pengumpulan</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                        <div class="bg-secondary-fixed h-full rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-sm mt-lg">
                        <div class="bg-white/10 p-md rounded-lg text-center">
                            <p class="text-2xl font-bold">{{ $submittedA }}</p>
                            <p class="text-[10px] uppercase tracking-wider opacity-80">Dikumpul</p>
                        </div>
                        <div class="bg-white/10 p-md rounded-lg text-center">
                            <p class="text-2xl font-bold">{{ $gradedA }}</p>
                            <p class="text-[10px] uppercase tracking-wider opacity-80">Dinilai</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-10">
                <i class="ti ti-clipboard-list text-[120px]"></i>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-slate-200 rounded-xl overflow-hidden">
            <div class="p-md border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h4 class="font-label-md text-primary">Informasi Penting</h4>
                <i class="ti ti-info-circle text-slate-400 text-[18px]"></i>
            </div>
            <div class="p-md space-y-md">
                <div class="flex gap-md">
                    <div class="shrink-0 w-2 h-2 rounded-full bg-secondary mt-2"></div>
                    <p class="text-body-sm text-on-surface-variant">Nilai yang sudah disimpan akan langsung terlihat oleh mahasiswa.</p>
                </div>
                <div class="flex gap-md">
                    <div class="shrink-0 w-2 h-2 rounded-full bg-error mt-2"></div>
                    <p class="text-body-sm text-on-surface-variant">Anda dapat menilai meskipun mahasiswa belum mengumpulkan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterTugas(filter) {
    const cards = document.querySelectorAll('.assignment-card');
    const buttons = { all: document.getElementById('btn-all'), submitted: document.getElementById('btn-submitted'), unsubmitted: document.getElementById('btn-unsubmitted') };
    Object.values(buttons).forEach(b => { b.className = 'px-lg py-2 rounded-md text-slate-500 hover:text-primary font-label-md text-sm'; });
    buttons[filter] && (buttons[filter].className = 'px-lg py-2 rounded-md bg-white shadow-sm text-primary font-label-md text-sm');
    cards.forEach(card => {
        const s = card.dataset.status;
        if (filter === 'all') card.style.display = '';
        else if (filter === 'submitted') card.style.display = (s === 'submitted' || s === 'graded') ? '' : 'none';
        else if (filter === 'unsubmitted') card.style.display = s === 'unsubmitted' ? '' : 'none';
    });
}
</script>
@endpush
@endsection
