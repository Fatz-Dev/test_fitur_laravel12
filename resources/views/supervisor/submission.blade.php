@extends('layouts.app')
@section('title', 'Detail Pengumpulan')
@section('content')

{{-- Breadcrumb & Nav --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-md mb-lg">
    <nav class="flex items-center gap-2 text-label-sm text-outline">
        <a href="{{ route('supervisor.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
        <i class="ti ti-chevron-right text-[13px]"></i>
        <a href="{{ route('supervisor.classes.show', $school) }}" class="hover:text-primary transition-colors">{{ $school->name }}</a>
        <i class="ti ti-chevron-right text-[13px]"></i>
        <a href="{{ route('supervisor.students.assignments', [$school, $profile]) }}" class="hover:text-primary transition-colors">Tugas</a>
        <i class="ti ti-chevron-right text-[13px]"></i>
        <span class="text-on-surface font-semibold">Detail Pengumpulan</span>
    </nav>
    <a href="{{ route('supervisor.students.assignments', [$school, $profile]) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-on-surface-variant text-label-md hover:bg-slate-50 transition-all active:scale-95">
        <i class="ti ti-arrow-left text-[16px]"></i>
        <span>Kembali</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-lg items-start">

    {{-- Left: Main content --}}
    <div class="lg:col-span-8 flex flex-col gap-lg">

        {{-- Student & Assignment Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-lg">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-md">
                <div class="flex gap-md">
                    <div class="w-16 h-16 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-xl ring-2 ring-slate-100 flex-shrink-0">
                        {{ strtoupper(substr($profile->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="font-h3 text-blue-900">{{ $profile->user->name }}</h2>
                        <p class="text-body-md text-slate-500">NIM: {{ $profile->nim }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            @if($submission->submitted_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="ti ti-circle-check text-[14px] mr-1"></i>
                                    Diserahkan {{ $submission->submitted_at->format('d M Y, H:i') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    <i class="ti ti-clock text-[14px] mr-1"></i>
                                    Belum dikumpulkan
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-left md:text-right">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Judul Tugas</p>
                    <h3 class="font-label-md text-primary-container">{{ $assignment->title }}</h3>
                    <p class="text-body-sm text-slate-500 mt-1 flex items-center gap-1 justify-end">
                        <i class="ti ti-clock text-[14px] {{ $assignment->isPastDeadline() ? 'text-error' : 'text-emerald-600' }}"></i>
                        <span class="{{ $assignment->isPastDeadline() ? 'text-error' : '' }}">
                            Tenggat: {{ $assignment->deadline->format('d M Y, H:i') }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Assignment instructions --}}
        @if($assignment->description || $assignment->instructions || $assignment->attachment_path)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-lg">
            <h3 class="font-label-md text-on-surface mb-md flex items-center gap-2">
                <i class="ti ti-clipboard-list text-primary text-[18px]"></i>
                Detail Tugas
            </h3>
            @if($assignment->description)
                <p class="text-body-sm text-on-surface-variant mb-md">{{ $assignment->description }}</p>
            @endif
            @if($assignment->instructions)
                <div class="bg-surface-container-low border border-surface-variant rounded-lg p-md mb-md">
                    <p class="text-[12px] font-label-md text-on-surface-variant uppercase tracking-wider mb-1">Petunjuk Pengerjaan</p>
                    <p class="text-[13px] text-on-surface whitespace-pre-line">{{ $assignment->instructions }}</p>
                </div>
            @endif
            @if($assignment->attachment_path)
                <a href="{{ Storage::url($assignment->attachment_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 border border-secondary text-secondary px-md py-2 rounded-lg text-sm hover:bg-secondary/5 transition-colors font-label-md">
                    <i class="ti ti-paperclip text-[16px]"></i>
                    Unduh Lampiran Tugas
                </a>
            @endif
        </div>
        @endif

        {{-- Submission file/notes --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-md bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ti ti-upload text-secondary text-[18px]"></i>
                    <span class="text-sm font-semibold text-slate-700">Pengumpulan Mahasiswa</span>
                </div>
                @if($submission->file_path)
                    <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                       class="flex items-center gap-1 px-3 py-1.5 bg-secondary text-white rounded text-xs font-bold hover:opacity-90 transition-colors">
                        <i class="ti ti-download text-[14px]"></i>
                        Unduh File
                    </a>
                @endif
            </div>
            <div class="p-lg">
                @if($submission->submitted_at)
                    @if($submission->notes)
                        <div class="mb-md">
                            <p class="text-[12px] font-label-md text-on-surface-variant uppercase tracking-wider mb-1">Catatan Mahasiswa</p>
                            <p class="text-[13px] text-on-surface bg-surface-container-low border border-surface-variant rounded-lg p-md whitespace-pre-line">{{ $submission->notes }}</p>
                        </div>
                    @endif
                    @if($submission->file_path)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-md flex items-center gap-3">
                            <i class="ti ti-file text-[32px] text-secondary opacity-70"></i>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">File Terlampir</p>
                                <a href="{{ Storage::url($submission->file_path) }}" target="_blank"
                                   class="text-[12px] text-secondary hover:underline">{{ basename($submission->file_path) }}</a>
                            </div>
                        </div>
                    @else
                        <p class="text-[13px] text-on-surface-variant italic">Tidak ada file terlampir.</p>
                    @endif
                @else
                    <div class="text-center py-xl">
                        <i class="ti ti-inbox text-[48px] opacity-30 block mb-2 text-on-surface-variant"></i>
                        <p class="font-body-sm text-on-surface-variant">Mahasiswa belum mengumpulkan tugas ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Grading panel --}}
    <div class="lg:col-span-4 flex flex-col gap-lg">
        <div class="bg-white rounded-xl shadow-md border border-slate-200 p-lg sticky top-24">
            <h3 class="font-h3 text-blue-900 mb-md">Penilaian</h3>

            @if($submission->graded_at)
                <div class="flex items-center gap-2 text-[12px] text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg px-md py-sm mb-md">
                    <i class="ti ti-check text-[14px]"></i>
                    Dinilai pada {{ $submission->graded_at->format('d M Y, H:i') }}
                </div>
            @endif

            <form method="POST" action="{{ route('supervisor.submissions.grade', [$school, $profile, $assignment]) }}" class="flex flex-col gap-lg">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nilai Akhir (0–100)</label>
                    <div class="relative">
                        <input name="grade" type="number" min="0" max="100" required
                               value="{{ old('grade', $submission->grade) }}"
                               placeholder="0"
                               class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-lg font-bold text-primary focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition-all">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">/ 100</div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-2">Pastikan nilai sesuai dengan rubrik yang berlaku.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Komentar / Feedback</label>
                    <textarea name="comment" rows="5"
                              placeholder="Berikan saran atau catatan untuk mahasiswa..."
                              class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-secondary focus:border-transparent outline-none transition-all resize-none">{{ old('comment', $submission->comment) }}</textarea>
                </div>

                @if(!$submission->submitted_at)
                    <div class="p-md bg-amber-50 border border-amber-100 rounded-lg">
                        <div class="flex gap-2">
                            <i class="ti ti-alert-triangle text-amber-600 text-sm"></i>
                            <p class="text-[11px] text-amber-800 leading-tight">Mahasiswa belum mengumpulkan. Anda tetap dapat memberikan nilai.</p>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col gap-md">
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 bg-primary text-white rounded-lg font-bold hover:bg-primary-container transition-all shadow-md active:scale-[0.98]">
                        <i class="ti ti-check text-[16px]"></i>
                        {{ $submission->grade !== null ? 'Perbarui Nilai' : 'Simpan Nilai' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
