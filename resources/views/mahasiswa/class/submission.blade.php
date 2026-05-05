@extends('layouts.app')
@section('title', 'Pengumpulan Tugas')
@section('content')

<div class="mb-lg">
    <a href="{{ route('mahasiswa.class.assignments', $registration) }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors mb-md">
        <i class="ti ti-arrow-left text-[16px]"></i> Kembali ke Daftar Tugas
    </a>
    <h2 class="font-h2 text-h2 text-primary">{{ $assignment->title }}</h2>
    <p class="font-body-sm text-on-surface-variant mt-1">{{ $registration->school->name }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-lg">

    {{-- Left: Assignment detail --}}
    <div class="space-y-lg">
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
                    <p class="text-[12px] font-label-md text-on-surface-variant uppercase tracking-wider mb-1">Petunjuk Pengerjaan</p>
                    <p class="text-[13px] text-on-surface whitespace-pre-line">{{ $assignment->instructions }}</p>
                </div>
            @endif

            <div class="flex items-center gap-2 text-[13px] mb-md">
                <i class="ti ti-clock text-[15px] {{ $assignment->isPastDeadline() ? 'text-error' : 'text-emerald-600' }}"></i>
                <span class="{{ $assignment->isPastDeadline() ? 'text-error' : 'text-emerald-700' }} font-medium">
                    Tenggat: {{ $assignment->deadline->format('d M Y H:i') }}
                    @if($assignment->isPastDeadline()) (Sudah lewat) @else (Masih berlaku) @endif
                </span>
            </div>

            @if($assignment->attachment_path)
                <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 border border-secondary text-secondary px-md py-2 rounded-lg text-sm hover:bg-secondary/5 transition-colors font-label-md">
                    <i class="ti ti-file-download text-[18px]"></i>
                    Unduh Lampiran Tugas
                </a>
            @endif
        </div>

        {{-- Grade / Feedback --}}
        @if($submission->grade !== null)
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-lg shadow-sm">
                <h3 class="font-label-md text-emerald-800 mb-md flex items-center gap-2">
                    <i class="ti ti-star-filled text-emerald-600 text-[18px]"></i>
                    Penilaian dari Supervisor
                </h3>
                <div class="flex items-center gap-md">
                    <div class="text-center bg-white border border-emerald-200 rounded-xl px-xl py-md">
                        <p class="text-[11px] text-emerald-600 font-medium uppercase tracking-wider">Nilai</p>
                        <p class="font-h1 text-emerald-700" style="font-size:48px; line-height:1">{{ $submission->grade }}</p>
                        <p class="text-[12px] text-emerald-600">/100</p>
                    </div>
                    @if($submission->comment)
                        <div class="flex-1">
                            <p class="text-[12px] font-label-md text-emerald-700 mb-1">Komentar:</p>
                            <p class="text-[13px] text-emerald-800 bg-white border border-emerald-100 rounded-lg p-md whitespace-pre-line">{{ $submission->comment }}</p>
                        </div>
                    @endif
                </div>
                <p class="text-[11px] text-emerald-600 mt-md flex items-center gap-1">
                    <i class="ti ti-clock text-[12px]"></i>
                    Dinilai {{ $submission->graded_at?->format('d M Y H:i') }}
                    @if($submission->grader) oleh {{ $submission->grader->name }} @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Right: Submission form --}}
    <div>
        <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm sticky top-20">
            <h3 class="font-label-md text-on-surface mb-md flex items-center gap-2">
                <i class="ti ti-upload text-secondary text-[18px]"></i>
                @if($submission->submitted_at) Perbarui Pengumpulan @else Kumpulkan Tugas @endif
            </h3>

            @if($submission->submitted_at)
                <div class="flex items-center gap-2 text-[12px] text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg px-md py-sm mb-md">
                    <i class="ti ti-circle-check text-[15px]"></i>
                    Terakhir dikumpulkan: {{ $submission->submitted_at->format('d M Y H:i') }}
                </div>
                @if($submission->file_path)
                    <div class="mb-md">
                        <p class="text-[12px] text-on-surface-variant mb-1">File saat ini:</p>
                        <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                           class="inline-flex items-center gap-1 text-[12px] text-secondary hover:underline font-medium">
                            <i class="ti ti-paperclip text-[13px]"></i>
                            Lihat file pengumpulan
                        </a>
                    </div>
                @endif
            @endif

            @if($assignment->isPastDeadline() && !$submission->submitted_at)
                <div class="bg-error/10 border border-error/30 rounded-lg px-md py-sm text-[12px] text-error flex items-start gap-2 mb-md">
                    <i class="ti ti-alert-circle text-[15px] flex-shrink-0 mt-0.5"></i>
                    <span>Tenggat telah lewat. Anda tidak dapat mengumpulkan tugas ini.</span>
                </div>
            @else
                <form method="POST"
                      action="{{ route('mahasiswa.class.submit', [$registration, $assignment]) }}"
                      enctype="multipart/form-data"
                      class="space-y-md">
                    @csrf

                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block">Catatan / Keterangan</label>
                        <textarea name="notes" rows="4"
                                  placeholder="Tuliskan catatan atau keterangan tentang pengumpulan Anda..."
                                  class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none">{{ old('notes', $submission->notes) }}</textarea>
                    </div>

                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block">
                            Upload File
                            @if($submission->submitted_at)
                                <span class="text-[11px] font-normal text-on-surface-variant">(opsional jika sudah ada)</span>
                            @endif
                        </label>
                        <input name="file" type="file"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                               class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary outline-none bg-white file:mr-3 file:py-1 file:px-3 file:border-0 file:text-sm file:bg-primary/10 file:text-primary file:rounded file:font-medium">
                        <p class="text-[12px] text-on-surface-variant">PDF, Word, JPG, PNG, ZIP — maks 10 MB</p>
                    </div>

                    <button type="submit"
                            class="w-full bg-secondary text-white py-2.5 rounded-lg font-label-md text-sm hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                        <i class="ti ti-upload text-[16px]"></i>
                        {{ $submission->submitted_at ? 'Perbarui Pengumpulan' : 'Kumpulkan Sekarang' }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
