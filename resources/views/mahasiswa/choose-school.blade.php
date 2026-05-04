@extends('layouts.app')
@section('title', 'Pilih Sekolah ' . $program)
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Rekomendasi Lokasi {{ $program }}</h2>
        <p class="font-body-sm text-on-surface-variant">
            Lokasi dalam radius <strong>{{ $radius }} km</strong> dari tempat tinggal Anda.
        </p>
    </div>
    <a href="{{ route('mahasiswa.dashboard') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors font-label-md">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali
    </a>
</div>

@if($schools->isEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-lg flex items-start gap-md">
        <span class="material-symbols-outlined text-amber-600 text-[24px] flex-shrink-0">warning</span>
        <div>
            <p class="font-label-md text-amber-900 text-sm">Tidak ada lokasi tersedia</p>
            <p class="font-body-sm text-amber-800 mt-1">
                Tidak ada lokasi {{ $program }} dalam radius {{ $radius }} km dari tempat tinggal Anda.
                Hubungi admin untuk informasi lebih lanjut.
            </p>
        </div>
    </div>
@else
    <form method="POST" action="{{ route('mahasiswa.program.register', strtolower($program)) }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-gutter">
            @foreach($schools as $s)
                <label class="block bg-white border-2 rounded-xl p-lg cursor-pointer transition-all
                              {{ $s->slots <= 0 ? 'opacity-60 cursor-not-allowed border-slate-200' : 'border-slate-200 hover:border-secondary' }}">
                    <div class="flex items-start justify-between gap-md">
                        <div class="flex-1 min-w-0">
                            <p class="font-label-md text-on-surface">{{ $s->name }}</p>
                            <p class="text-[12px] text-on-surface-variant mt-1">
                                {{ $s->jenjang }} &middot; {{ $s->address }}
                            </p>
                        </div>
                        <input type="radio" name="school_id" value="{{ $s->id }}" required
                               @if($s->slots <= 0) disabled @endif
                               class="mt-1 accent-[#006a61] flex-shrink-0">
                    </div>
                    <div class="mt-md flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 text-[12px] px-2 py-1 rounded-full bg-primary/10 text-primary font-medium">
                            <span class="material-symbols-outlined text-[14px]">near_me</span>
                            {{ number_format($s->distance, 2) }} km
                        </span>
                        @if($s->slots > 0)
                            <span class="inline-flex items-center gap-1 text-[12px] px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-medium">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                {{ $s->slots }} slot tersedia
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[12px] px-2 py-1 rounded-full bg-error/10 text-error font-medium">
                                <span class="material-symbols-outlined text-[14px]">block</span>
                                Slot penuh
                            </span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>

        @error('school_id')
            <p class="text-sm text-error mt-md flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">error</span>{{ $message }}
            </p>
        @enderror

        <div class="mt-lg flex justify-end">
            <button class="bg-primary hover:bg-primary-container text-white font-label-md py-2 px-lg rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">send</span>
                Daftar Lokasi Terpilih
            </button>
        </div>
    </form>
@endif
@endsection
