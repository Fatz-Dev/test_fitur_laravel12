@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
{{-- Welcome Header --}}
<section class="flex flex-col md:flex-row md:items-end justify-between gap-md mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Ringkasan Dashboard</h2>
        <p class="font-body-md text-body-md text-on-surface-variant">Pantau aktivitas akademik dan penempatan lokasi secara real-time.</p>
    </div>
    <div class="flex gap-md">
        <a href="{{ route('admin.mahasiswa.index') }}?status=pending"
           class="px-md py-2 bg-secondary text-white rounded-lg font-label-md hover:opacity-90 transition-opacity flex items-center gap-2 text-sm">
            <span class="material-symbols-outlined text-[18px]">pending_actions</span>
            Review Pending
        </a>
    </div>
</section>

{{-- Stats Cards --}}
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-lg">
    <div class="bg-white p-lg rounded-xl shadow-[0_2px_4px_rgba(0,35,111,0.05)] border border-slate-200 flex items-start gap-4">
        <div class="bg-blue-50 p-3 rounded-lg flex-shrink-0">
            <span class="material-symbols-outlined text-primary text-[28px]">groups</span>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Total Mahasiswa</p>
            <h3 class="font-h3 text-h3 text-primary mt-1">{{ $stats['mahasiswa_total'] }}</h3>
            <p class="text-[12px] text-on-surface-variant font-medium mt-1">Terdaftar dalam sistem</p>
        </div>
    </div>

    <div class="bg-white p-lg rounded-xl shadow-[0_2px_4px_rgba(0,35,111,0.05)] border border-slate-200 flex items-start gap-4">
        <div class="bg-amber-50 p-3 rounded-lg flex-shrink-0">
            <span class="material-symbols-outlined text-amber-600 text-[28px]">pending_actions</span>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Menunggu Review</p>
            <h3 class="font-h3 text-h3 text-primary mt-1">{{ $stats['mahasiswa_pending'] }}</h3>
            @if($stats['mahasiswa_pending'] > 0)
                <p class="text-[12px] text-amber-600 font-medium mt-1">Perlu tindakan segera</p>
            @else
                <p class="text-[12px] text-secondary font-medium mt-1">Semua sudah ditinjau</p>
            @endif
        </div>
    </div>

    <div class="bg-white p-lg rounded-xl shadow-[0_2px_4px_rgba(0,35,111,0.05)] border border-slate-200 flex items-start gap-4">
        <div class="bg-secondary/10 p-3 rounded-lg flex-shrink-0">
            <span class="material-symbols-outlined text-secondary text-[28px]">school</span>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Sekolah Terdaftar</p>
            <h3 class="font-h3 text-h3 text-primary mt-1">{{ $stats['schools'] }}</h3>
            <p class="text-[12px] text-on-surface-variant font-medium mt-1">Lokasi KPM &amp; PPL</p>
        </div>
    </div>

    <div class="bg-white p-lg rounded-xl shadow-[0_2px_4px_rgba(0,35,111,0.05)] border border-slate-200 flex items-start gap-4">
        <div class="bg-blue-50 p-3 rounded-lg flex-shrink-0">
            <span class="material-symbols-outlined text-blue-600 text-[28px]">assignment_turned_in</span>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Penempatan Pending</p>
            <h3 class="font-h3 text-h3 text-primary mt-1">{{ $stats['registrations_pending'] }}</h3>
            <p class="text-[12px] text-blue-600 font-medium mt-1">Menunggu konfirmasi</p>
        </div>
    </div>
</section>

{{-- Main Data --}}
<section class="grid grid-cols-1 lg:grid-cols-2 gap-gutter mb-lg">
    {{-- Recent Mahasiswa --}}
    <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-lg">
            <div>
                <h3 class="font-h3 text-h3 text-primary">Mahasiswa Terbaru</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Registrasi terkini yang masuk</p>
            </div>
            <a href="{{ route('admin.mahasiswa.index') }}"
               class="text-secondary font-label-md hover:underline text-sm flex items-center gap-1">
                Lihat Semua
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
        <div class="space-y-2">
            @forelse($recentMahasiswa as $m)
                <div class="flex items-center justify-between p-md hover:bg-slate-50 rounded-lg transition-colors">
                    <div class="flex items-center gap-md">
                        <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($m->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <a href="{{ route('admin.mahasiswa.show', $m) }}"
                               class="font-label-md text-on-surface hover:text-primary transition-colors">
                                {{ $m->user->name }}
                            </a>
                            <p class="text-[12px] text-on-surface-variant">{{ $m->nim }}</p>
                        </div>
                    </div>
                    @php $badges = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-error/10 text-error']; @endphp
                    <span class="text-[12px] px-2 py-1 rounded-full font-medium {{ $badges[$m->status] ?? 'bg-slate-100 text-slate-600' }} capitalize">
                        {{ $m->status }}
                    </span>
                </div>
            @empty
                <div class="text-center py-8 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[40px] opacity-30">group</span>
                    <p class="font-body-sm mt-2">Belum ada mahasiswa terdaftar.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Registrations --}}
    <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-lg">
            <div>
                <h3 class="font-h3 text-h3 text-primary">Penempatan Terbaru</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Status penempatan KPM &amp; PPL</p>
            </div>
            <a href="{{ route('admin.registrations.index') }}"
               class="text-secondary font-label-md hover:underline text-sm flex items-center gap-1">
                Lihat Semua
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
        <div class="space-y-2">
            @forelse($recentRegistrations as $r)
                <div class="flex items-center justify-between p-md hover:bg-slate-50 rounded-lg transition-colors border-l-4
                    {{ $r->program === 'KPM' ? 'border-amber-400' : 'border-blue-400' }}">
                    <div class="min-w-0 flex-1 pr-4">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $r->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $r->program }}
                            </span>
                            <p class="font-label-md text-on-surface truncate">{{ $r->mahasiswaProfile->user->name ?? '-' }}</p>
                        </div>
                        <p class="text-[12px] text-on-surface-variant truncate mt-0.5">{{ $r->school->name }}</p>
                    </div>
                    @php $badges = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-error/10 text-error','cancelled'=>'bg-slate-100 text-slate-600']; @endphp
                    <span class="text-[12px] px-2 py-1 rounded-full font-medium flex-shrink-0 {{ $badges[$r->status] ?? 'bg-slate-100 text-slate-600' }} capitalize">
                        {{ $r->status }}
                    </span>
                </div>
            @empty
                <div class="text-center py-8 text-on-surface-variant">
                    <span class="material-symbols-outlined text-[40px] opacity-30">assignment</span>
                    <p class="font-body-sm mt-2">Belum ada penempatan.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Bottom Banner --}}
<section class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
    <div class="bg-blue-900 rounded-xl p-xl text-white relative overflow-hidden">
        <div class="relative z-10">
            <h4 class="font-h3 text-h3 mb-md">Panduan Admin SIPEP</h4>
            <p class="text-blue-100 mb-lg max-w-md text-sm">Kelola mahasiswa, lokasi, dan penempatan KPM/PPL dengan mudah menggunakan fitur-fitur yang tersedia.</p>
            <a href="{{ route('admin.settings.edit') }}"
               class="inline-flex items-center gap-2 bg-teal-400 text-blue-900 px-lg py-2 rounded-lg font-label-md hover:bg-teal-300 transition-all text-sm">
                <span class="material-symbols-outlined text-[18px]">settings</span>
                Pengaturan Sistem
            </a>
        </div>
        <span class="material-symbols-outlined absolute -bottom-8 -right-8 text-blue-800 opacity-50" style="font-size:160px">menu_book</span>
    </div>

    <div class="bg-surface-container-high rounded-xl p-xl flex items-center gap-xl">
        <div class="flex-1">
            <h4 class="font-h3 text-h3 text-primary mb-md">Statistik Program</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-body-sm text-on-surface-variant">Lokasi KPM</span>
                    <span class="font-label-md text-on-surface">{{ $stats['schools'] }} lokasi</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-body-sm text-on-surface-variant">Mahasiswa Disetujui</span>
                    <span class="font-label-md text-secondary">{{ $stats['mahasiswa_total'] - $stats['mahasiswa_pending'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-body-sm text-on-surface-variant">Menunggu Review</span>
                    <span class="font-label-md text-amber-600">{{ $stats['mahasiswa_pending'] }}</span>
                </div>
            </div>
            <a href="{{ route('admin.mahasiswa.index') }}"
               class="mt-lg inline-flex items-center gap-1 text-primary font-label-md hover:underline text-sm">
                <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                Kelola Mahasiswa
            </a>
        </div>
    </div>
</section>
@endsection
