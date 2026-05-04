@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<h1 class="text-2xl font-bold mb-4">Dashboard Admin</h1>

<div class="grid md:grid-cols-4 gap-4 mb-6">
    @php
    $cards = [
        ['Mahasiswa', $stats['mahasiswa_total'], 'indigo'],
        ['Menunggu Review', $stats['mahasiswa_pending'], 'amber'],
        ['Sekolah Terdaftar', $stats['schools'], 'emerald'],
        ['Penempatan Pending', $stats['registrations_pending'], 'rose'],
    ];
    @endphp
    @foreach($cards as [$label, $value, $color])
        <div class="bg-white border border-slate-200 rounded p-4">
            <p class="text-xs text-slate-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-{{ $color }}-700">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white border border-slate-200 rounded p-4">
        <h2 class="font-semibold mb-3">Mahasiswa Terbaru</h2>
        <table class="w-full text-sm">
            <tbody class="divide-y">
            @forelse($recentMahasiswa as $m)
                <tr>
                    <td class="py-2">
                        <a href="{{ route('admin.mahasiswa.show', $m) }}" class="text-indigo-600 hover:underline">
                            {{ $m->user->name }}
                        </a>
                        <span class="text-xs text-slate-500"> &middot; {{ $m->nim }}</span>
                    </td>
                    <td class="text-right">
                        @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$m->status]; @endphp
                        <span class="text-xs px-2 py-1 rounded bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">{{ $m->status }}</span>
                    </td>
                </tr>
            @empty
                <tr><td class="py-3 text-sm text-slate-500">Belum ada mahasiswa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="bg-white border border-slate-200 rounded p-4">
        <h2 class="font-semibold mb-3">Penempatan Terbaru</h2>
        <table class="w-full text-sm">
            <tbody class="divide-y">
            @forelse($recentRegistrations as $r)
                <tr>
                    <td class="py-2">
                        <span class="font-semibold">{{ $r->program }}</span> &middot;
                        {{ $r->mahasiswaProfile->user->name ?? '-' }} &rarr;
                        {{ $r->school->name }}
                    </td>
                    <td class="text-right">
                        @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose','cancelled'=>'slate'][$r->status]; @endphp
                        <span class="text-xs px-2 py-1 rounded bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">{{ $r->status }}</span>
                    </td>
                </tr>
            @empty
                <tr><td class="py-3 text-sm text-slate-500">Belum ada penempatan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
