@extends('layouts.app')
@section('title', 'Pengumpulan Tugas')
@section('content')

{{-- Breadcrumb --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <nav class="flex items-center gap-2 text-sm font-medium">
        <a href="{{ route('mahasiswa.class.index') }}" class="text-slate-500 hover:text-primary transition-colors">Kelas Saya</a>
        <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
        <a href="{{ route('mahasiswa.class.assignments', $registration) }}" class="text-slate-500 hover:text-primary transition-colors">Tugas</a>
        <i class="ti ti-chevron-right text-slate-300 text-xs"></i>
        <span class="text-primary font-bold">Detail Pengumpulan</span>
    </nav>
    <a href="{{ route('mahasiswa.class.assignments', $registration) }}"
       class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-on-surface-variant font-semibold text-sm hover:bg-slate-50 transition-all active:scale-95">
        <i class="ti ti-arrow-left text-base"></i>
        <span>Kembali</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    {{-- ─ Left: Assignment info + submission area ─ --}}
    <div class="lg:col-span-8 flex flex-col gap-6">

        {{-- Student & assignment header card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex gap-4">
                    <div class="w-16 h-16 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-2xl ring-2 ring-slate-100 shrink-0">
                        {{ strtoupper(substr($profile->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-blue-900">{{ $profile->user->name }}</h2>
                        <p class="text-sm text-slate-500">NIM: {{ $profile->nim }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            @if($submission->submitted_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    <i class="ti ti-circle-check text-sm mr-1"></i>
                                    Diserahkan {{ $submission->submitted_at->format('d M Y, H:i') }} WIB
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    <i class="ti ti-clock text-sm mr-1"></i>
                                    Belum dikumpulkan
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-left md:text-right shrink-0">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Judul Tugas</p>
                    <h3 class="font-bold text-primary text-base">{{ $assignment->title }}</h3>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 justify-end">
                        <i class="ti ti-clock text-sm {{ $assignment->isPastDeadline() ? 'text-error' : 'text-emerald-600' }}"></i>
                        <span class="{{ $assignment->isPastDeadline() ? 'text-error' : '' }}">
                            Tenggat: {{ $assignment->deadline->format('d M Y, H:i') }} WIB
                        </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Assignment detail --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-semibold text-on-surface mb-4 flex items-center gap-2">
                <i class="ti ti-clipboard-list text-primary text-lg"></i>
                Detail Tugas
            </h3>

            @if($assignment->description)
                <p class="text-sm text-on-surface-variant mb-4">{{ $assignment->description }}</p>
            @endif

            @if($assignment->instructions)
                <div class="bg-surface-container-low border border-surface-variant rounded-lg p-4 mb-4">
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Petunjuk Pengerjaan</p>
                    <p class="text-sm text-on-surface whitespace-pre-line">{{ $assignment->instructions }}</p>
                </div>
            @endif

            @if($assignment->attachment_path)
                <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 border border-secondary text-secondary px-4 py-2 rounded-lg text-sm hover:bg-secondary/5 transition-colors font-semibold">
                    <i class="ti ti-file-download text-lg"></i>
                    Unduh Lampiran Tugas
                </a>
            @endif
        </div>

        {{-- Submission file area --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ti ti-upload text-secondary text-lg"></i>
                    <span class="text-sm font-semibold text-slate-700">File Pengumpulan Anda</span>
                </div>
                @if($submission->file_path)
                    <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                       class="flex items-center gap-1 px-3 py-1.5 bg-secondary text-white rounded text-xs font-bold hover:opacity-90 transition-colors">
                        <i class="ti ti-download text-sm"></i>
                        Unduh
                    </a>
                @endif
            </div>
            <div class="p-6">
                @if($submission->submitted_at)
                    @if($submission->notes)
                        <div class="mb-4">
                            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">Catatan Anda</p>
                            <p class="text-sm text-on-surface bg-surface-container-low border border-surface-variant rounded-lg p-4 whitespace-pre-line">{{ $submission->notes }}</p>
                        </div>
                    @endif
                    @if($submission->file_path)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex items-center gap-3">
                            <i class="ti ti-file text-4xl text-secondary opacity-70"></i>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">File Terlampir</p>
                                <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                                   class="text-xs text-secondary hover:underline">{{ basename($submission->file_path) }}</a>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-on-surface-variant italic">Tidak ada file terlampir.</p>
                    @endif
                @else
                    <div class="text-center py-12">
                        <i class="ti ti-inbox text-6xl opacity-30 block mb-2 text-on-surface-variant"></i>
                        <p class="text-sm text-on-surface-variant">Belum ada pengumpulan. Gunakan form di sebelah kanan untuk mengumpulkan.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Grade display --}}
        @if($submission->grade !== null)
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 shadow-sm">
                <h3 class="font-semibold text-emerald-800 mb-4 flex items-center gap-2">
                    <i class="ti ti-star-filled text-emerald-600 text-lg"></i>
                    Penilaian dari Supervisor
                </h3>
                <div class="flex items-center gap-6">
                    <div class="text-center bg-white border border-emerald-200 rounded-xl px-8 py-4">
                        <p class="text-xs text-emerald-600 font-bold uppercase tracking-wider">Nilai</p>
                        <p class="font-bold text-emerald-700" style="font-size:48px; line-height:1">{{ $submission->grade }}</p>
                        <p class="text-xs text-emerald-600">/100</p>
                    </div>
                    @if($submission->comment)
                        <div class="flex-1">
                            <p class="text-xs font-bold text-emerald-700 mb-1">Komentar:</p>
                            <p class="text-sm text-emerald-800 bg-white border border-emerald-100 rounded-lg p-4 whitespace-pre-line">{{ $submission->comment }}</p>
                        </div>
                    @endif
                </div>
                <p class="text-xs text-emerald-600 mt-4 flex items-center gap-1">
                    <i class="ti ti-clock text-xs"></i>
                    Dinilai {{ $submission->graded_at?->format('d M Y, H:i') }}
                    @if($submission->grader) oleh {{ $submission->grader->name }} @endif
                </p>
            </div>
        @endif
    </div>

    {{-- ─ Right: Submission form panel ─ --}}
    <div class="lg:col-span-4 flex flex-col gap-6">
        <div class="bg-white rounded-xl shadow-md border border-slate-200 p-6 sticky top-24">
            <h3 class="text-xl font-bold text-blue-900 mb-4">
                {{ $submission->submitted_at ? 'Perbarui Pengumpulan' : 'Kumpulkan Tugas' }}
            </h3>

            @if($submission->submitted_at)
                <div class="flex items-center gap-2 text-xs text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 mb-4">
                    <i class="ti ti-circle-check text-sm"></i>
                    Terakhir dikumpulkan: {{ $submission->submitted_at->format('d M Y, H:i') }}
                </div>
            @endif

            @if($assignment->isPastDeadline() && !$submission->submitted_at)
                <div class="bg-error/10 border border-error/30 rounded-lg px-4 py-3 text-sm text-error flex items-start gap-2 mb-4">
                    <i class="ti ti-alert-circle text-base shrink-0 mt-0.5"></i>
                    <span>Tenggat telah lewat. Anda tidak dapat mengumpulkan tugas ini.</span>
                </div>
            @else
                <form method="POST"
                      action="{{ route('mahasiswa.class.submit', [$registration, $assignment]) }}"
                      enctype="multipart/form-data"
                      class="flex flex-col gap-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Catatan / Keterangan</label>
                        <textarea name="notes" rows="5"
                                  placeholder="Tuliskan catatan atau keterangan tentang pengumpulan Anda..."
                                  class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-secondary focus:border-transparent outline-none resize-none transition-all">{{ old('notes', $submission->notes) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Upload File
                            @if($submission->submitted_at)
                                <span class="text-xs font-normal text-on-surface-variant">(opsional jika sudah ada)</span>
                            @endif
                        </label>
                        <input name="file" type="file"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip"
                               class="w-full border border-slate-200 rounded-lg px-4 py-3 text-sm bg-white focus:ring-2 focus:ring-secondary outline-none
                                      file:mr-3 file:py-1 file:px-3 file:border-0 file:text-sm file:bg-primary/10 file:text-primary file:rounded file:font-semibold cursor-pointer">
                        <p class="text-xs text-on-surface-variant mt-1.5">PDF, Word, JPG, PNG, ZIP — maks 10 MB</p>
                    </div>

                    <div class="h-px bg-slate-100"></div>

                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 bg-primary text-white rounded-lg font-bold hover:bg-blue-800 transition-all shadow-md active:scale-[0.98]">
                        <i class="ti ti-upload text-base"></i>
                        {{ $submission->submitted_at ? 'Perbarui Pengumpulan' : 'Kumpulkan Sekarang' }}
                    </button>
                </form>
            @endif

            <div class="mt-4 p-4 bg-amber-50 border border-amber-100 rounded-lg">
                <div class="flex gap-2">
                    <i class="ti ti-info-circle text-amber-600 text-sm mt-0.5"></i>
                    <p class="text-xs text-amber-800 leading-relaxed">Pengumpulan yang sudah ada akan tergantikan jika Anda mengupload file baru.</p>
                </div>
            </div>
        </div>

        {{-- Status stats card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col gap-3">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Tugas Ini</h4>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600">Status</span>
                <span class="text-sm font-bold {{ $submission->submitted_at ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $submission->submitted_at ? 'Dikumpulkan' : 'Belum Dikumpulkan' }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600">Nilai</span>
                <span class="text-sm font-bold text-primary">
                    {{ $submission->grade !== null ? $submission->grade . '/100' : '—' }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600">Program</span>
                <span class="text-sm font-bold text-on-surface">{{ $registration->program }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
