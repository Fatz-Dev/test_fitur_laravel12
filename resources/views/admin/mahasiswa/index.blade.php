@extends('layouts.app')
@section('title', 'Daftar Mahasiswa')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Daftar Mahasiswa</h2>
        <p class="font-body-sm text-on-surface-variant">Kelola dan tinjau data mahasiswa yang terdaftar</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="flex flex-wrap gap-3 mb-lg">
    <div class="relative">
        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIM / email"
               class="pl-10 pr-4 py-2 border border-outline-variant rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-secondary/30 bg-white w-64"/>
    </div>
    <select name="status" class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30">
        <option value="">Semua Status</option>
        @foreach(['pending', 'approved', 'rejected'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="bg-primary text-white text-sm px-md py-2 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-1">
        <i class="ti ti-adjustments-horizontal text-[16px]"></i> Filter
    </button>
</form>

{{-- Table --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                <tr>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Mahasiswa</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">NIM</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Email</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Microteaching</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Status</th>
                    <th class="px-md py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($mahasiswas as $m)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-md py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($m->user->name, 0, 1)) }}
                            </div>
                            <a href="{{ route('admin.mahasiswa.show', $m) }}" class="font-label-md text-on-surface hover:text-primary transition-colors">
                                {{ $m->user->name }}
                            </a>
                        </div>
                    </td>
                    <td class="px-md py-3 font-body-sm text-on-surface">{{ $m->nim }}</td>
                    <td class="px-md py-3 font-body-sm text-on-surface-variant">{{ $m->user->email }}</td>
                    <td class="px-md py-3">
                        <span class="font-label-md text-secondary">{{ $m->microteaching_grade }}</span>
                    </td>
                    <td class="px-md py-3">
                        @php $badges = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-error/10 text-error']; @endphp
                        <span class="text-[12px] px-2 py-1 rounded-full font-medium {{ $badges[$m->status] ?? 'bg-slate-100 text-slate-600' }} capitalize">
                            {{ $m->status }}
                        </span>
                    </td>
                    <td class="px-md py-3 text-right">
                        <a href="{{ route('admin.mahasiswa.show', $m) }}"
                           class="inline-flex items-center gap-1 text-[12px] text-primary hover:underline font-medium">
                            Detail
                            <i class="ti ti-arrow-right text-[14px]"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-on-surface-variant">
                        <i class="ti ti-users text-[48px] opacity-30 block mb-2"></i>
                        <p class="font-body-sm">Belum ada data mahasiswa.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-md">{{ $mahasiswas->links() }}</div>
@endsection
