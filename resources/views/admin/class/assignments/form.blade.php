@extends('layouts.app')
@section('title', $assignment->exists ? 'Edit Tugas' : 'Tambah Tugas')
@section('content')

<div class="mb-lg">
    <a href="{{ route('admin.class.assignments.index') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors mb-md">
        <i class="ti ti-arrow-left text-[16px]"></i> Kembali ke Daftar Tugas
    </a>
    <h2 class="font-h2 text-h2 text-primary">{{ $assignment->exists ? 'Edit Tugas' : 'Tambah Tugas Baru' }}</h2>
    <p class="font-body-sm text-on-surface-variant">Tugas ini akan muncul di semua mahasiswa yang sudah ditempatkan</p>
</div>

<form method="POST"
      action="{{ $assignment->exists ? route('admin.class.assignments.update', $assignment) : route('admin.class.assignments.store') }}"
      enctype="multipart/form-data"
      class="max-w-2xl space-y-lg">
    @csrf
    @if($assignment->exists) @method('PUT') @endif

    <div class="bg-white border border-slate-200 rounded-xl p-xl shadow-sm space-y-lg">

        {{-- Title --}}
        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">
                Judul Tugas <span class="text-error">*</span>
            </label>
            <input name="title" type="text" required maxlength="255"
                   value="{{ old('title', $assignment->title) }}"
                   placeholder="Contoh: Laporan Minggu Pertama KPM"
                   class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
        </div>

        {{-- Description --}}
        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">Deskripsi Singkat</label>
            <textarea name="description" rows="3"
                      placeholder="Gambaran singkat tentang tugas ini..."
                      class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none">{{ old('description', $assignment->description) }}</textarea>
        </div>

        {{-- Instructions --}}
        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">Petunjuk Pengerjaan</label>
            <textarea name="instructions" rows="5"
                      placeholder="Tuliskan langkah-langkah pengerjaan, format file, dan hal yang perlu diperhatikan..."
                      class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none">{{ old('instructions', $assignment->instructions) }}</textarea>
        </div>

        {{-- Deadline --}}
        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">
                Tenggat Pengumpulan <span class="text-error">*</span>
            </label>
            <input name="deadline" type="datetime-local" required
                   value="{{ old('deadline', $assignment->exists ? $assignment->deadline->format('Y-m-d\TH:i') : '') }}"
                   class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
            <p class="text-[12px] text-on-surface-variant">Mahasiswa tidak dapat mengumpulkan setelah tenggat ini.</p>
        </div>

        {{-- Attachment --}}
        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">File Lampiran (opsional)</label>
            @if($assignment->attachment_path)
                <div class="flex items-center gap-2 p-sm bg-surface-container-low rounded-lg border border-surface-variant text-sm mb-2">
                    <i class="ti ti-paperclip text-[16px] text-secondary"></i>
                    <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank"
                       class="text-secondary hover:underline">File lampiran saat ini</a>
                    <span class="text-on-surface-variant text-[12px]">(upload baru untuk mengganti)</span>
                </div>
            @endif
            <input name="attachment" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                   class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary outline-none bg-white file:mr-3 file:py-1 file:px-3 file:border-0 file:text-sm file:bg-primary/10 file:text-primary file:rounded file:font-medium">
            <p class="text-[12px] text-on-surface-variant">PDF, Word, JPG, PNG — maks 5 MB</p>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit"
                class="bg-primary text-white px-xl py-2 rounded-lg font-label-md text-sm hover:bg-primary-container transition-colors">
            {{ $assignment->exists ? 'Simpan Perubahan' : 'Tambah Tugas' }}
        </button>
        <a href="{{ route('admin.class.assignments.index') }}"
           class="px-xl py-2 border border-outline-variant rounded-lg font-label-md text-sm hover:bg-slate-50 transition-colors">
            Batal
        </a>
    </div>
</form>
@endsection
