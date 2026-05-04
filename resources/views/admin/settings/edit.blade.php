@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<h1 class="text-2xl font-bold mb-4">Pengaturan</h1>

<form method="POST" action="{{ route('admin.settings.update') }}"
      class="bg-white border border-slate-200 rounded p-6 max-w-2xl space-y-4">
    @csrf @method('PUT')

    <div>
        <label class="block text-sm font-medium mb-1">Nama Institusi</label>
        <input name="institution_name" value="{{ old('institution_name', $settings['institution_name']) }}"
               class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Radius Maksimum (km)</label>
        <input name="max_radius_km" type="number" step="0.1" min="0.1" max="1000"
               value="{{ old('max_radius_km', $settings['max_radius_km']) }}" required
               class="w-full border border-slate-300 rounded px-3 py-2">
        <p class="text-xs text-slate-500 mt-1">
            Sistem mencari sekolah terdekat dalam radius ini dari domisili mahasiswa (Kondisi 1).
            Jika tidak ada, sistem beralih ke Kondisi 2 (kedekatan antar sekolah KPM &amp; PPL).
        </p>
    </div>

    <div class="bg-indigo-50 border border-indigo-200 rounded p-3 text-xs text-indigo-700">
        Batas waktu pendaftaran (deadline) dikelola per gelombang di menu
        <a href="{{ route('admin.gelombang.index') }}" class="font-semibold underline">Gelombang</a>.
    </div>

    <div class="flex justify-end pt-2">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded">
            Simpan Pengaturan
        </button>
    </div>
</form>
@endsection
