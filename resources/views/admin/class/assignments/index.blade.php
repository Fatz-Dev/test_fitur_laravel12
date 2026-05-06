@extends('layouts.app')
@section('title', 'Tugas SIPEP Class')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Tugas SIPEP Class</h2>
        <p class="font-body-sm text-on-surface-variant">Kelola tugas yang diberikan kepada mahasiswa KPM &amp; PPL</p>
    </div>
    <a href="{{ route('admin.class.assignments.create') }}"
       class="inline-flex items-center gap-2 bg-primary text-white text-sm px-md py-2 rounded-lg hover:bg-primary-container transition-colors font-label-md">
        <i class="ti ti-plus text-[18px]"></i>
        Tambah Tugas
    </a>
</div>

{{-- Filter Golongan --}}
<div class="bg-white border border-slate-200 rounded-xl px-lg py-md mb-md flex items-center gap-sm flex-wrap shadow-sm">
    <span class="text-label-sm text-on-surface-variant font-medium mr-1">Filter Golongan:</span>
    <a href="{{ route('admin.class.assignments.index') }}"
       class="px-4 py-1.5 rounded-full text-label-sm font-medium transition-all
              {{ !$filterProgram ? 'bg-primary text-white shadow' : 'bg-slate-100 text-on-surface-variant hover:bg-slate-200' }}">
        Semua
    </a>
    <a href="{{ route('admin.class.assignments.index', ['program' => 'KPM']) }}"
       class="px-4 py-1.5 rounded-full text-label-sm font-medium transition-all
              {{ $filterProgram === 'KPM' ? 'bg-amber-500 text-white shadow' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
        KPM
    </a>
    <a href="{{ route('admin.class.assignments.index', ['program' => 'PPL']) }}"
       class="px-4 py-1.5 rounded-full text-label-sm font-medium transition-all
              {{ $filterProgram === 'PPL' ? 'bg-blue-600 text-white shadow' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
        PPL
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                <tr>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Judul Tugas</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Golongan</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Tenggat</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Pengumpulan</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Dibuat</th>
                    <th class="px-md py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($assignments as $a)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-md py-3">
                        <p class="font-label-md text-on-surface">{{ $a->title }}</p>
                        @if($a->description)
                            <p class="text-[12px] text-on-surface-variant mt-0.5 line-clamp-1">{{ $a->description }}</p>
                        @endif
                    </td>
                    <td class="px-md py-3">
                        @if($a->program === 'KPM')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">KPM</span>
                        @elseif($a->program === 'PPL')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">PPL</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500">Semua</span>
                        @endif
                    </td>
                    <td class="px-md py-3">
                        @if($a->isPastDeadline())
                            <span class="inline-flex items-center gap-1 text-[12px] bg-error/10 text-error px-2 py-0.5 rounded-full font-medium">
                                <i class="ti ti-clock-x text-[13px]"></i>
                                {{ $a->deadline->format('d M Y H:i') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[12px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                <i class="ti ti-clock text-[13px]"></i>
                                {{ $a->deadline->format('d M Y H:i') }}
                            </span>
                        @endif
                    </td>
                    <td class="px-md py-3">
                        <span class="font-label-md text-secondary">{{ $a->submissions_count }}</span>
                        <span class="text-[12px] text-on-surface-variant"> pengumpulan</span>
                    </td>
                    <td class="px-md py-3 text-[12px] text-on-surface-variant">
                        {{ $a->created_at->format('d M Y') }}
                    </td>
                    <td class="px-md py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.class.assignments.edit', $a) }}"
                               class="inline-flex items-center gap-1 text-[12px] text-primary hover:underline font-medium">
                                <i class="ti ti-edit text-[14px]"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.class.assignments.destroy', $a) }}"
                                  onsubmit="return confirm('Hapus tugas ini?')">
                                @csrf @method('DELETE')
                                <button class="text-[12px] text-error hover:underline font-medium">
                                    <i class="ti ti-trash text-[14px]"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-on-surface-variant">
                        <i class="ti ti-clipboard-list text-[48px] opacity-30 block mb-2"></i>
                        <p class="font-body-sm">
                            @if($filterProgram)
                                Belum ada tugas untuk golongan {{ $filterProgram }}.
                            @else
                                Belum ada tugas. Tambahkan tugas pertama.
                            @endif
                        </p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-md">{{ $assignments->links() }}</div>
@endsection
