@extends('layouts.app')
@section('title', 'Daftar Sekolah')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Sekolah</h1>
    <a href="{{ route('admin.schools.create') }}"
       class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded">+ Tambah Sekolah</a>
</div>

<div class="bg-white border border-slate-200 rounded overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-xs text-slate-500 border-b bg-slate-50">
            <tr>
                <th class="px-3 py-2">Nama</th>
                <th>Jenjang</th>
                <th>Program</th>
                <th>Kuota KPM/PPL</th>
                <th>Lokasi</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y">
        @forelse($schools as $s)
            <tr>
                <td class="px-3 py-2 font-medium">{{ $s->name }}</td>
                <td>{{ $s->jenjang }}</td>
                <td>{{ $s->program }}</td>
                <td>{{ $s->kuota_kpm }} / {{ $s->kuota_ppl }}</td>
                <td class="text-xs text-slate-500">
                    <a target="_blank" class="hover:underline"
                       href="https://www.google.com/maps?q={{ $s->latitude }},{{ $s->longitude }}">
                        {{ $s->latitude }}, {{ $s->longitude }}
                    </a>
                </td>
                <td>
                    <span class="text-xs px-2 py-1 rounded {{ $s->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="text-right pr-3 space-x-2">
                    <a href="{{ route('admin.schools.edit', $s) }}" class="text-xs text-indigo-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('admin.schools.destroy', $s) }}" class="inline"
                          onsubmit="return confirm('Hapus sekolah?');">
                        @csrf @method('DELETE')
                        <button class="text-xs text-rose-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-6 text-slate-500">Belum ada sekolah.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $schools->links() }}</div>
@endsection
