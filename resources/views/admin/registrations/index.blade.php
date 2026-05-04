@extends('layouts.app')
@section('title', 'Penempatan KPM & PPL')
@section('content')

<div class="flex items-center justify-between mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Penempatan KPM &amp; PPL</h2>
        <p class="font-body-sm text-on-surface-variant">Kelola dan konfirmasi penempatan mahasiswa</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="flex flex-wrap gap-3 mb-lg">
    <select name="program" class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30">
        <option value="">Semua Program</option>
        @foreach(['KPM','PPL'] as $p)
            <option value="{{ $p }}" @selected(request('program')===$p)>{{ $p }}</option>
        @endforeach
    </select>
    <select name="gelombang_id" class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30">
        <option value="">Semua Gelombang</option>
        @foreach($gelombangList as $g)
            <option value="{{ $g->id }}" @selected(request('gelombang_id')==(string)$g->id)>
                {{ $g->program }} — {{ $g->label() }}
            </option>
        @endforeach
    </select>
    <select name="status" class="border border-outline-variant rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-secondary/30">
        <option value="">Semua Status</option>
        @foreach(['pending','approved','rejected','cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="bg-primary text-white text-sm px-md py-2 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-[16px]">filter_list</span> Filter
    </button>
</form>

<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left border-b border-slate-200 bg-surface-container-low">
                <tr>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Mahasiswa</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Program</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Gelombang</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Lokasi</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Jarak</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Status</th>
                    <th class="px-md py-3 font-label-sm text-on-surface-variant uppercase tracking-wider">Diajukan</th>
                    <th class="px-md py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($registrations as $r)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-md py-3">
                        <p class="font-label-md text-on-surface">{{ $r->mahasiswaProfile->user->name ?? '-' }}</p>
                        <p class="text-[12px] text-on-surface-variant">{{ $r->mahasiswaProfile->nim }}</p>
                    </td>
                    <td class="px-md py-3">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $r->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $r->program }}
                        </span>
                    </td>
                    <td class="px-md py-3 text-[12px] text-on-surface-variant">
                        {{ $r->gelombang ? $r->gelombang->label() : '-' }}
                    </td>
                    <td class="px-md py-3">
                        <p class="font-label-md text-on-surface">{{ $r->school->name }}</p>
                    </td>
                    <td class="px-md py-3 text-[12px] text-on-surface-variant">{{ number_format($r->distance_km, 2) }} km</td>
                    <td class="px-md py-3">
                        @php $badges = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-error/10 text-error','cancelled'=>'bg-slate-100 text-slate-600']; @endphp
                        <span class="text-[12px] px-2 py-1 rounded-full font-medium {{ $badges[$r->status] ?? 'bg-slate-100 text-slate-600' }} capitalize">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td class="px-md py-3 text-[12px] text-on-surface-variant">{{ $r->created_at->format('d M Y') }}</td>
                    <td class="px-md py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($r->status === 'pending')
                                <form method="POST" action="{{ route('admin.registrations.approve', $r) }}" class="inline">
                                    @csrf
                                    <button class="text-[12px] text-secondary hover:underline font-medium">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('admin.registrations.reject', $r) }}" class="inline"
                                      onsubmit="this.querySelector('[name=note]').value = prompt('Alasan penolakan?') || '';">
                                    @csrf
                                    <input type="hidden" name="note">
                                    <button class="text-[12px] text-error hover:underline font-medium">Tolak</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.registrations.destroy', $r) }}" class="inline"
                                  onsubmit="return confirm('Hapus penempatan ini?');">
                                @csrf @method('DELETE')
                                <button class="text-[12px] text-on-surface-variant hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-on-surface-variant">
                        <span class="material-symbols-outlined text-[48px] opacity-30 block mb-2">assignment</span>
                        <p class="font-body-sm">Belum ada data penempatan.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-md">{{ $registrations->links() }}</div>
@endsection
