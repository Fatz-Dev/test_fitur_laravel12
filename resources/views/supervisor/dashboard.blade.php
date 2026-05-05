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
    <div class="bg-white border border-blue-800 rounded-xl p-lg shadow-sm flex items-center gap-md">
        <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-map-lock text-primary text-[24px]"></i>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Lokasi Ditangani</p>
            <p class="font-h2 text-h2 text-black">{{ $schools->count() }}</p>
        </div>
    </div>
    <div class="bg-white border border-blue-800 rounded-xl p-lg shadow-sm flex items-center gap-md">
        <div class="h-12 w-12 rounded-xl bg-secondary/10 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-users text-secondary text-[24px]"></i>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Total Mahasiswa</p>
            <p class="font-h2 text-h2 text-secondary">{{ $totalMahasiswa }}</p>
        </div>
    </div>
    <div class="bg-white border border-blue-800 rounded-xl p-lg shadow-sm flex items-center gap-md">
        <div class="h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="ti ti-clipboard-check text-amber-600 text-[24px]"></i>
        </div>
        <div>
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Menunggu Penilaian</p>
            <p class="font-h2 text-h2 text-amber-600">{{ $pendingGrades }}</p>
        </div>
    </div>
</div>
@endsection
