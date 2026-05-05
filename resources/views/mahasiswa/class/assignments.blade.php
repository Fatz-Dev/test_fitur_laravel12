@extends('layouts.app')
@section('title', 'Daftar Tugas')
@section('content')

{{-- Hero Header --}}
<section class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6 mb-6">
    <div class="space-y-2">
        <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('mahasiswa.class.index') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                <i class="ti ti-school text-sm"></i> Kelas Saya
            </a>
            <i class="ti ti-chevron-right text-xs"></i>
            <span class="text-primary font-semibold">Daftar Tugas</span>
        </nav>
        <h2 class="text-3xl font-bold text-on-surface">{{ $registration->school->name }}</h2>
        <p class="text-on-surface-variant text-sm">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold text-xs
                {{ $registration->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                {{ $registration->program }}
            </span>
            &nbsp;Pantau batas waktu dan kumpulkan tugas tepat waktu.
        </p>
    </div>
    <a href="{{ route('mahasiswa.class.index') }}"
       class="flex items-center gap-2 border border-slate-200 bg-white px-5 py-2.5 rounded-lg text-sm font-semibold text-on-surface-variant hover:bg-slate-50 transition-all shrink-0">
        <i class="ti ti-arrow-left text-base"></i>
        Kembali
    </a>
</section>

@if($assignments->isEmpty())
    <div class="bg-white border border-slate-200 rounded-xl p-12 text-center text-on-surface-variant">
        <i class="ti ti-clipboard-list text-6xl opacity-30 block mb-3"></i>
        <p class="text-base">Belum ada tugas dari admin.</p>
        <p class="text-sm mt-1">Pantau halaman ini secara berkala.</p>
    </div>
@else

    @php
        $submittedCount = collect($assignments)->filter(fn($a) => isset($submissions[$a->id]) && $submissions[$a->id]->submitted_at)->count();
        $gradedCount    = collect($assignments)->filter(fn($a) => isset($submissions[$a->id]) && $submissions[$a->id]->grade !== null)->count();
        $pct = $assignments->count() > 0 ? round($submittedCount / $assignments->count() * 100) : 0;
    @endphp

    {{-- Filters + main grid --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

        {{-- ─ Left: filter bar + list ─ --}}
        <div class="md:col-span-8 space-y-6">

            {{-- Filter bar --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center p-1 bg-slate-100 rounded-lg">
                    <button onclick="filterTugas('all')" id="btn-all"
                            class="px-5 py-2 rounded-md bg-white shadow-sm text-primary font-semibold text-sm">Semua</button>
                    <button onclick="filterTugas('pending')" id="btn-pending"
                            class="px-5 py-2 rounded-md text-slate-500 hover:text-primary font-semibold text-sm">Belum Dikumpul</button>
                    <button onclick="filterTugas('submitted')" id="btn-submitted"
                            class="px-5 py-2 rounded-md text-slate-500 hover:text-primary font-semibold text-sm">Selesai</button>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Total:</span>
                    <span class="font-bold text-primary text-sm">{{ $assignments->count() }} tugas</span>
                </div>
            </div>

            {{-- Assignment list --}}
            <div class="space-y-4" id="assignment-list">
                @foreach($assignments as $a)
                    @php
                        $sub = $submissions[$a->id] ?? null;
                        $status = 'pending';
                        if ($sub && $sub->grade !== null) $status = 'submitted';
                        elseif ($sub && $sub->submitted_at) $status = 'submitted';
                    @endphp

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:border-primary/30 transition-all flex flex-col md:flex-row gap-6 items-start md:items-center assignment-card"
                         data-status="{{ $status }}">

                        {{-- Icon --}}
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0
                            {{ $sub && $sub->submitted_at ? 'bg-emerald-50 text-emerald-600' : ($a->isPastDeadline() ? 'bg-error/10 text-error' : 'bg-blue-50 text-primary') }}">
                            <i class="ti {{ $sub && $sub->submitted_at ? 'ti-circle-check' : ($a->isPastDeadline() ? 'ti-clock-x' : 'ti-clipboard-list') }} text-2xl"></i>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0 space-y-1">
                            <h3 class="font-semibold text-xl text-on-surface">{{ $a->title }}</h3>
                            <div class="flex flex-wrap items-center gap-4 text-sm text-on-surface-variant">
                                @if($a->description)
                                    <span class="flex items-center gap-1 line-clamp-1">
                                        <i class="ti ti-file-description text-sm"></i>{{ Str::limit($a->description, 60) }}
                                    </span>
                                @endif
                                <span class="flex items-center gap-1 {{ $a->isPastDeadline() && !($sub && $sub->submitted_at) ? 'text-error font-medium' : '' }}">
                                    <i class="ti ti-timer text-base"></i>
                                    Deadline: {{ $a->deadline->format('d M Y, H:i') }} WIB
                                </span>
                            </div>
                            @if($sub && $sub->comment)
                                <p class="text-xs bg-surface-container-low border border-surface-variant rounded-lg px-3 py-2 mt-2 text-on-surface-variant">
                                    <span class="font-semibold">Feedback:</span> {{ $sub->comment }}
                                </p>
                            @endif
                        </div>

                        {{-- Action --}}
                        <div class="flex flex-col items-end gap-2 shrink-0 w-full md:w-auto">
                            @if($sub && $sub->grade !== null)
                                <span class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">
                                    Nilai: {{ $sub->grade }}/100
                                </span>
                            @elseif($sub && $sub->submitted_at)
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">Dikumpulkan</span>
                            @elseif($a->isPastDeadline())
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">Tenggat Lewat</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-semibold">Belum Dikerjakan</span>
                            @endif

                            <a href="{{ route('mahasiswa.class.submission', [$registration, $a]) }}"
                               class="flex items-center justify-center gap-2 bg-secondary text-on-secondary px-6 py-2 rounded-lg text-sm font-semibold hover:opacity-90 transition-all w-full md:w-auto">
                                {{ ($sub && $sub->submitted_at) ? 'Lihat Tugas' : 'Kumpulkan' }}
                                <i class="ti ti-arrow-right text-sm"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ─ Right: Summary sidebar ─ --}}
        <div class="md:col-span-4 space-y-6">

            {{-- Progress card --}}
            <div class="bg-primary text-on-primary p-6 rounded-xl shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold mb-1">Ringkasan Tugas</h3>
                    <p class="text-white/70 text-xs">{{ $registration->school->name }}</p>
                    <div class="space-y-4 mt-6">
                        <div class="flex justify-between text-sm mb-1">
                            <span>Progress Semester</span>
                            <span class="font-bold">{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-white/20 h-2 rounded-full overflow-hidden">
                            <div class="bg-secondary-fixed h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div class="bg-white/10 p-4 rounded-lg text-center">
                                <p class="text-2xl font-bold">{{ $submittedCount }}</p>
                                <p class="text-xs uppercase tracking-wider opacity-80">Selesai</p>
                            </div>
                            <div class="bg-white/10 p-4 rounded-lg text-center">
                                <p class="text-2xl font-bold">{{ $assignments->count() - $submittedCount }}</p>
                                <p class="text-xs uppercase tracking-wider opacity-80">Menunggu</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-10 pointer-events-none">
                    <i class="ti ti-clipboard-list" style="font-size: 120px;"></i>
                </div>
            </div>

            {{-- Info card --}}
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h4 class="font-semibold text-primary text-sm">Informasi Penting</h4>
                    <i class="ti ti-info-circle text-slate-400 text-lg"></i>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex gap-3">
                        <div class="shrink-0 w-2 h-2 rounded-full bg-error mt-2"></div>
                        <p class="text-sm text-on-surface-variant">Pastikan format file pengumpulan adalah <strong>PDF, Word, atau ZIP</strong> maks 10MB.</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="shrink-0 w-2 h-2 rounded-full bg-secondary mt-2"></div>
                        <p class="text-sm text-on-surface-variant">Anda dapat memperbarui pengumpulan selama tenggat belum lewat.</p>
                    </div>
                    @if($gradedCount > 0)
                    <div class="flex gap-3">
                        <div class="shrink-0 w-2 h-2 rounded-full bg-primary mt-2"></div>
                        <p class="text-sm text-on-surface-variant"><strong>{{ $gradedCount }}</strong> tugas sudah dinilai oleh supervisor.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tips card --}}
            <div class="bg-surface-container border border-primary/10 p-6 rounded-xl">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-primary shadow-sm">
                        <i class="ti ti-bulb text-lg"></i>
                    </div>
                    <span class="font-semibold text-primary text-sm">Tips Akademik</span>
                </div>
                <p class="italic text-sm text-primary/80">"Kerjakan tugas yang memiliki bobot nilai paling besar dan deadline terdekat terlebih dahulu untuk manajemen waktu yang lebih baik."</p>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
function filterTugas(filter) {
    const cards = document.querySelectorAll('.assignment-card');
    const btns = {
        all:       document.getElementById('btn-all'),
        pending:   document.getElementById('btn-pending'),
        submitted: document.getElementById('btn-submitted'),
    };
    const base = 'px-5 py-2 rounded-md font-semibold text-sm';
    Object.values(btns).forEach(b => { b.className = base + ' text-slate-500 hover:text-primary'; });
    btns[filter].className = base + ' bg-white shadow-sm text-primary';

    cards.forEach(card => {
        const s = card.dataset.status;
        if (filter === 'all')       card.style.display = '';
        else if (filter === 'submitted') card.style.display = s === 'submitted' ? '' : 'none';
        else if (filter === 'pending')   card.style.display = s === 'pending'   ? '' : 'none';
    });
}
</script>
@endpush
@endsection
