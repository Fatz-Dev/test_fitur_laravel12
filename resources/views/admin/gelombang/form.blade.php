@extends('layouts.app')
@section('title', $gelombang ? 'Edit Gelombang' : 'Tambah Gelombang')
@section('content')
<a href="{{ route('admin.gelombang.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
<h1 class="text-2xl font-bold mt-2 mb-4">{{ $gelombang ? 'Edit Gelombang' : 'Tambah Gelombang' }}</h1>

<form method="POST"
      action="{{ $gelombang ? route('admin.gelombang.update', $gelombang) : route('admin.gelombang.store') }}"
      class="bg-white border border-slate-200 rounded p-6 max-w-2xl space-y-4">
    @csrf
    @if($gelombang) @method('PUT') @endif

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Program</label>
            <select name="program" required class="w-full border border-slate-300 rounded px-3 py-2">
                @foreach(['KPM','PPL'] as $p)
                    <option value="{{ $p }}" @selected(old('program', $gelombang?->program) === $p)>{{ $p }}</option>
                @endforeach
            </select>
            @error('program') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nomor Gelombang</label>
            <input name="nomor" type="number" min="1" max="99" required
                   value="{{ old('nomor', $gelombang?->nomor) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2"
                   placeholder="1">
            @error('nomor') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Tahun Akademik</label>
        <input name="tahun_akademik" required
               value="{{ old('tahun_akademik', $gelombang?->tahun_akademik) }}"
               class="w-full border border-slate-300 rounded px-3 py-2"
               placeholder="2024/2025">
        @error('tahun_akademik') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Buka</label>
            <input name="tanggal_buka" type="date"
                   value="{{ old('tanggal_buka', $gelombang?->tanggal_buka?->format('Y-m-d')) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2">
            @error('tanggal_buka') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Tutup (Deadline)</label>
            <input name="tanggal_tutup" type="date"
                   value="{{ old('tanggal_tutup', $gelombang?->tanggal_tutup?->format('Y-m-d')) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2">
            @error('tanggal_tutup') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input name="is_active" type="checkbox" id="is_active" value="1"
               @checked(old('is_active', $gelombang?->is_active))
               class="rounded border-slate-300">
        <label for="is_active" class="text-sm font-medium">Aktifkan gelombang ini</label>
        <span class="text-xs text-slate-500">(Hanya satu gelombang aktif per program)</span>
    </div>

    <div class="flex justify-end pt-2">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded">
            {{ $gelombang ? 'Simpan Perubahan' : 'Tambah Gelombang' }}
        </button>
    </div>
</form>
@endsection
