@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')

<div class="mb-lg">
    <h2 class="font-h2 text-h2 text-primary">Pengaturan Sistem</h2>
    <p class="font-body-sm text-on-surface-variant">Konfigurasi parameter sistem penempatan KPM &amp; PPL</p>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.update') }}"
          class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm space-y-lg">
        @csrf @method('PUT')

        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">Nama Institusi</label>
            <div class="relative">
                <!-- <i class="ti ti-building absolute left-md top-1/2 -translate-y-1/2 text-outline text-[20px]"></i> -->
                <input name="institution_name" value="{{ old('institution_name', $settings['institution_name']) }}"
                       class="w-full pl-xl pr-md py-sm border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"/>
            </div>
        </div>

        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">Radius Maksimum (km)</label>
            <div class="relative">
                <!-- <i class="ti ti-antenna absolute left-md top-1/2 -translate-y-1/2 text-outline text-[20px]"></i> -->
                <input name="max_radius_km" type="number" step="0.1" min="0.1" max="1000"
                       value="{{ old('max_radius_km', $settings['max_radius_km']) }}" required
                       class="w-full pl-xl pr-md py-sm border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"/>
            </div>
            <div class="flex items-start gap-sm p-sm bg-surface-container-low rounded-lg border border-surface-variant">
                <i class="ti ti-info-circle text-primary text-[16px] mt-0.5"></i>
                <p class="text-[12px] text-on-primary-fixed-variant">
                    Sistem mencari lokasi terdekat dalam radius ini dari domisili mahasiswa (Kondisi 1). Jika tidak ada yang cocok, sistem beralih ke mode kedekatan antar-lokasi KPM &amp; PPL (Kondisi 2).
                </p>
            </div>
        </div>

        <div class="flex items-center gap-sm p-md bg-primary/5 border border-primary/20 rounded-xl">
            <i class="ti ti-calendar text-primary text-[20px]"></i>
            <p class="font-body-sm text-on-surface">
                Batas waktu pendaftaran (deadline) dikelola per gelombang di menu
                <a href="{{ route('admin.gelombang.index') }}" class="text-secondary font-label-md hover:underline">Gelombang</a>.
            </p>
        </div>

        <div class="flex justify-end pt-sm">
            <button class="bg-primary hover:bg-primary-container text-white font-label-md py-2 px-lg rounded-lg transition-colors flex items-center gap-2">
                <i class="ti ti-device-floppy text-[18px]"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
