@extends('layouts.app')
@section('title', 'Pilih Sekolah ' . $program)
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold">Rekomendasi Sekolah {{ $program }}</h1>
        <p class="text-sm text-slate-500">
            Sekolah dalam radius <strong>{{ $radius }} km</strong> dari tempat tinggal Anda.
        </p>
    </div>
    <a href="{{ route('mahasiswa.dashboard') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
</div>

@if($schools->isEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded p-4">
        <p class="text-sm text-amber-800">
            Tidak ada sekolah {{ $program }} yang tersedia dalam radius {{ $radius }} km dari lokasi Anda.
            Hubungi admin untuk informasi lebih lanjut.
        </p>
    </div>
@else
    <form method="POST" action="{{ route('mahasiswa.program.register', strtolower($program)) }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            @foreach($schools as $s)
                <label class="block bg-white border-2 rounded p-4 cursor-pointer hover:border-indigo-400
                              {{ $s->slots <= 0 ? 'opacity-60 cursor-not-allowed' : 'border-slate-200' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold">{{ $s->name }}</p>
                            <p class="text-xs text-slate-500">{{ $s->jenjang }} &middot; {{ $s->address }}</p>
                        </div>
                        <input type="radio" name="school_id" value="{{ $s->id }}" required
                               @if($s->slots <= 0) disabled @endif
                               class="mt-1 accent-indigo-600">
                    </div>
                    <div class="mt-3 flex gap-3 text-xs">
                        <span class="px-2 py-1 rounded bg-indigo-50 text-indigo-700">
                            {{ number_format($s->distance, 2) }} km
                        </span>
                        <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-700">
                            {{ $s->slots }} slot tersedia
                        </span>
                    </div>
                </label>
            @endforeach
        </div>

        @error('school_id') <p class="text-sm text-rose-600 mt-3">{{ $message }}</p> @enderror

        <div class="mt-6 flex justify-end">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded">
                Daftar Sekolah Terpilih
            </button>
        </div>
    </form>
@endif
@endsection
