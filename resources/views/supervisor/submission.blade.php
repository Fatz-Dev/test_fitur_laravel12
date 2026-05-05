@extends('layouts.app')
@section('title', 'Detail Pengumpulan')
@section('content')

<div class="mb-lg">
    <a href="{{ route('supervisor.students.assignments', [$school, $profile]) }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors mb-md">
        <i class="ti ti-arrow-left text-[16px]"></i> Kembali ke Daftar Tugas
    </a>
    <h2 class="font-h2 text-h2 text-primary">{{ $assignment->title }}</h2>
    <p class="font-body-sm text-on-surface-variant mt-1">
        Pengumpulan: <span class="font-medium text-on-surface">{{ $profile->user->name }}</span>
        ({{ $profile->nim }}) &bull; {{ $school->name }}
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">

    {{-- Left: Assignment info + submission --}}
    <div class="space-y-lg">
        {{-- Assignment Detail --}}
        <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm">
            <h3 class="font-label-md text-on-surface mb-md flex items-center gap-2">
                <i class="ti ti-clipboard-list text-primary text-[18px]"></i>
                Detail Tugas
            </h3>
            @if($assignment->description)
                <p class="text-[13px] text-on-surface-variant mb-md">{{ $assignment->description }}</p>
            @endif
            @if($assignment->instructions)
                <div class="bg-surface-container-low border border-surface-variant rounded-lg p-md mb-md">
                    <p class="text-[12px] font-label-md text-on-surface-variant uppercase tracking-wider mb-1">Petunjuk</p>
                    <p class="text-[13px] text-on-surface whitespace-pre-line">{{ $assignment->instructions }}</p>
                </div>
            @endif
            <div class="flex items-center gap-2 text-[13px]">
                <i class="ti ti-clock text-[15px] {{ $assignment->isPastDeadline() ? 'text-error' : 'text-emerald-600' }}"></i>
                <span class="{{ $assignment->isPastDeadline() ? 'text-error' : 'text-emerald-700' }}">
                    Tenggat: {{ $assignment->deadline->format('d M Y H:i') }}
                    {{ $assignment->isPastDeadline() ? '(Sudah lewat)' : '(Masih berlaku)' }}
                </span>
            </div>
            @if($assignment->attachment_path)
                <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank"
                   class="mt-md inline-flex items-center gap-1 text-[12px] text-secondary hover:underline font-medium">
                    <i class="ti ti-paperclip text-[14px]"></i>
                    Unduh Lampiran Tugas
                </a>
            @endif
        </div>

        {{-- Submission --}}
        <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm">
            <h3 class="font-label-md text-on-surface mb-md flex items-center gap-2">
                <i class="ti ti-upload text-secondary text-[18px]"></i>
                Pengumpulan Mahasiswa
            </h3>

            @if($submission->submitted_at)
                <div class="flex items-center gap-2 text-[12px] text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg px-md py-sm mb-md">
                    <i class="ti ti-circle-check text-[15px]"></i>
                    Dikumpulkan pada {{ $submission->submitted_at->format('d M Y H:i') }}
                </div>

                @if($submission->notes)
                    <div class="mb-md">
                        <p class="text-[12px] font-label-md text-on-surface-variant uppercase tracking-wider mb-1">Catatan Mahasiswa</p>
                        <p class="text-[13px] text-on-surface bg-surface-container-low border border-surface-variant rounded-lg p-md whitespace-pre-line">{{ $submission->notes }}</p>
                    </div>
                @endif

                @if($submission->file_path)
                    <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                       class="inline-flex items-center gap-2 border border-secondary text-secondary px-md py-2 rounded-lg text-sm hover:bg-secondary/5 transition-colors font-label-md">
                        <i class="ti ti-file-download text-[18px]"></i>
                        Unduh File Tugas
                    </a>
                @else
                    <p class="text-[13px] text-on-surface-variant italic">Tidak ada file terlampir (hanya catatan teks).</p>
                @endif
            @else
                <div class="text-center py-lg">
                    <i class="ti ti-inbox text-[48px] opacity-30 block mb-2 text-on-surface-variant"></i>
                    <p class="font-body-sm text-on-surface-variant">Mahasiswa belum mengumpulkan tugas ini.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Right: Grade form --}}
    <div>
        <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm sticky top-20">
            <h3 class="font-label-md text-on-surface mb-md flex items-center gap-2">
                <i class="ti ti-star text-amber-500 text-[18px]"></i>
                Penilaian
            </h3>

            @if($submission->graded_at)
                <div class="flex items-center gap-2 text-[12px] text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg px-md py-sm mb-md">
                    <i class="ti ti-check text-[14px]"></i>
                    Dinilai pada {{ $submission->graded_at->format('d M Y H:i') }}
                </div>
            @endif

            <form method="POST" action="{{ route('supervisor.submissions.grade', [$school, $profile, $assignment]) }}" class="space-y-md">
                @csrf

                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block">
                        Nilai <span class="text-error">*</span>
                        <span class="text-[12px] font-normal text-on-surface-variant">(0–100)</span>
                    </label>
                    <input name="grade" type="number" min="0" max="100" required
                           value="{{ old('grade', $submission->grade) }}"
                           placeholder="Masukkan nilai 0–100"
                           class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none text-lg font-bold">
                </div>

                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block">Komentar / Feedback</label>
                    <textarea name="comment" rows="4"
                              placeholder="Berikan feedback konstruktif kepada mahasiswa..."
                              class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none">{{ old('comment', $submission->comment) }}</textarea>
                </div>

                @if(!$submission->submitted_at)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg px-md py-sm text-[12px] text-amber-700 flex items-start gap-2">
                        <i class="ti ti-alert-triangle text-[15px] flex-shrink-0 mt-0.5"></i>
                        <span>Mahasiswa belum mengumpulkan. Anda tetap dapat memberikan nilai.</span>
                    </div>
                @endif

                <button type="submit"
                        class="w-full bg-primary text-white py-2.5 rounded-lg font-label-md text-sm hover:bg-primary-container transition-colors flex items-center justify-center gap-2">
                    <i class="ti ti-check text-[16px]"></i>
                    {{ $submission->grade !== null ? 'Perbarui Nilai' : 'Simpan Nilai' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
