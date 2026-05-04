@extends('layouts.app')
@section('title', $gelombang ? 'Edit Gelombang' : 'Tambah Gelombang')
@section('content')

<div class="mb-lg">
    <a href="{{ route('admin.gelombang.index') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors font-label-md">
        <i class="ti ti-arrow-left text-[18px]"></i> Kembali
    </a>
    <h2 class="font-h2 text-h2 text-primary mt-sm">{{ $gelombang ? 'Edit Gelombang' : 'Tambah Gelombang' }}</h2>
</div>

<div class="w-full">
    <form method="POST"
          action="{{ $gelombang ? route('admin.gelombang.update', $gelombang) : route('admin.gelombang.store') }}"
          class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm space-y-md">
        @csrf
        @if($gelombang) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-md">
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Program</label>
                <select name="program" required
                        class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all">
                    @foreach(['KPM','PPL'] as $p)
                        <option value="{{ $p }}" @selected(old('program', $gelombang?->program) === $p)>{{ $p }}</option>
                    @endforeach
                </select>
                @error('program') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Nomor Gelombang</label>
                <input name="nomor" type="number" min="1" max="99" required
                       value="{{ old('nomor', $gelombang?->nomor) }}"
                       placeholder="1"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"/>
                @error('nomor') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">Tahun Akademik</label>
            <input name="tahun_akademik" required
                   value="{{ old('tahun_akademik', $gelombang?->tahun_akademik) }}"
                   placeholder="2024/2025"
                   class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"/>
            @error('tahun_akademik') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid md:grid-cols-2 gap-md">
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Tanggal Buka</label>
                <input name="tanggal_buka" type="date"
                       value="{{ old('tanggal_buka', $gelombang?->tanggal_buka?->format('Y-m-d')) }}"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all"/>
                @error('tanggal_buka') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Tanggal Tutup (Deadline)</label>
                <input name="tanggal_tutup" type="date"
                       value="{{ old('tanggal_tutup', $gelombang?->tanggal_tutup?->format('Y-m-d')) }}"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all"/>
                @error('tanggal_tutup') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-sm p-md bg-surface-container-low rounded-lg border border-surface-variant">
            <input name="is_active" type="checkbox" id="is_active" value="1"
                   @checked(old('is_active', $gelombang?->is_active))
                   class="rounded border-outline-variant text-secondary focus:ring-secondary">
            <div>
                <label for="is_active" class="font-label-md text-on-surface text-sm cursor-pointer">Aktifkan gelombang ini</label>
                <p class="text-[12px] text-on-surface-variant">Hanya satu gelombang aktif per program yang diperbolehkan</p>
            </div>
        </div>

        <div class="flex justify-end pt-sm">
            <button class="bg-primary hover:bg-primary-container text-white font-label-md py-2 px-lg rounded-lg transition-colors flex items-center gap-2">
                <i class="ti ti-device-floppy text-[18px]"></i>
                {{ $gelombang ? 'Simpan Perubahan' : 'Tambah Gelombang' }}
            </button>
        </div>
    </form>
</div>
@endsection
