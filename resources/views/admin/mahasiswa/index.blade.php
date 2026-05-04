@extends('layouts.app')
@section('title', 'Daftar Mahasiswa')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Daftar Mahasiswa</h1>
</div>

<form method="GET" class="flex gap-2 mb-4">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama / NIM / email"
           class="border border-slate-300 rounded px-3 py-2 text-sm">
    <select name="status" class="border border-slate-300 rounded px-3 py-2 text-sm">
        <option value="">Semua Status</option>
        @foreach(['pending', 'approved', 'rejected'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="bg-slate-700 text-white text-sm px-4 rounded hover:bg-slate-800">Filter</button>
</form>

<div class="bg-white border border-slate-200 rounded overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-xs text-slate-500 border-b bg-slate-50">
            <tr>
                <th class="px-3 py-2">Nama</th>
                <th>NIM</th>
                <th>Email</th>
                <th>Microteaching</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="divide-y">
        @forelse($mahasiswas as $m)
            <tr>
                <td class="px-3 py-2 font-medium">{{ $m->user->name }}</td>
                <td>{{ $m->nim }}</td>
                <td class="text-slate-500">{{ $m->user->email }}</td>
                <td>{{ $m->microteaching_grade }}</td>
                <td>
                    @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$m->status]; @endphp
                    <span class="text-xs px-2 py-1 rounded bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">{{ $m->status }}</span>
                </td>
                <td class="text-right pr-3">
                    <a href="{{ route('admin.mahasiswa.show', $m) }}" class="text-indigo-600 hover:underline text-xs">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-6 text-slate-500">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $mahasiswas->links() }}</div>
@endsection
