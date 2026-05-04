@extends('layouts.app')
@section('title', 'Detail Mahasiswa')
@section('content')
<a href="{{ route('admin.mahasiswa.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
<h1 class="text-2xl font-bold mt-2 mb-4">{{ $mahasiswa->user->name }}</h1>

<div class="grid md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border border-slate-200 rounded p-4 md:col-span-2 space-y-2 text-sm">
        <p><span class="text-slate-500">Email:</span> {{ $mahasiswa->user->email }}</p>
        <p><span class="text-slate-500">NIM:</span> {{ $mahasiswa->nim }}</p>
        <p><span class="text-slate-500">No. HP:</span> {{ $mahasiswa->phone ?? '-' }}</p>
        <p><span class="text-slate-500">Alamat:</span> {{ $mahasiswa->address }}</p>
        <p><span class="text-slate-500">Koordinat:</span> {{ $mahasiswa->latitude }}, {{ $mahasiswa->longitude }}
            <a class="text-xs text-indigo-600 hover:underline" target="_blank"
               href="https://www.google.com/maps?q={{ $mahasiswa->latitude }},{{ $mahasiswa->longitude }}">(lihat peta)</a>
        </p>
        <p><span class="text-slate-500">Nilai Microteaching:</span> <strong>{{ $mahasiswa->microteaching_grade }}</strong></p>
        <p><span class="text-slate-500">Berkas:</span></p>
        <ul class="list-disc pl-5 text-indigo-600">
            @if($mahasiswa->transkrip_path)
                <li><a target="_blank" href="{{ asset('storage/'.$mahasiswa->transkrip_path) }}">Transkrip</a></li>
            @endif
            @if($mahasiswa->ktm_path)
                <li><a target="_blank" href="{{ asset('storage/'.$mahasiswa->ktm_path) }}">Kartu Tanda Mahasiswa</a></li>
            @endif
            @if($mahasiswa->surat_pengantar_path)
                <li><a target="_blank" href="{{ asset('storage/'.$mahasiswa->surat_pengantar_path) }}">Surat Pengantar</a></li>
            @endif
            @if($mahasiswa->pas_foto_path)
                <li><a target="_blank" href="{{ asset('storage/'.$mahasiswa->pas_foto_path) }}">Pas Foto</a></li>
            @endif
        </ul>
    </div>

    <div class="bg-white border border-slate-200 rounded p-4">
        <p class="text-xs text-slate-500">Status Saat Ini</p>
        @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$mahasiswa->status]; @endphp
        <p class="text-lg font-semibold text-{{ $sc }}-700 capitalize">{{ $mahasiswa->status }}</p>
        @if($mahasiswa->admin_note)
            <p class="text-xs text-slate-600 mt-2">Catatan: {{ $mahasiswa->admin_note }}</p>
        @endif

        @if($mahasiswa->status !== 'approved')
        <hr class="my-4">
        <form method="POST" action="{{ route('admin.mahasiswa.approve', $mahasiswa) }}" class="space-y-2">
            @csrf
            <textarea name="admin_note" rows="2" placeholder="Catatan (opsional)"
                      class="w-full border border-slate-300 rounded px-2 py-1 text-sm"></textarea>
            <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm py-2 rounded">
                Setujui &amp; Tetapkan Penempatan
            </button>
        </form>
        @endif

        @if($mahasiswa->status !== 'rejected')
        <form method="POST" action="{{ route('admin.mahasiswa.reject', $mahasiswa) }}" class="space-y-2 mt-3">
            @csrf
            <textarea name="admin_note" rows="2" required placeholder="Alasan penolakan"
                      class="w-full border border-slate-300 rounded px-2 py-1 text-sm"></textarea>
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white text-sm py-2 rounded">Tolak</button>
        </form>
        @endif

        <form method="POST" action="{{ route('admin.mahasiswa.destroy', $mahasiswa) }}" class="mt-3"
              onsubmit="return confirm('Hapus mahasiswa beserta akunnya?');">
            @csrf @method('DELETE')
            <button class="w-full text-xs text-rose-600 hover:underline">Hapus mahasiswa</button>
        </form>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded p-4">
    <h2 class="font-semibold mb-3">Riwayat Penempatan</h2>
    @if($mahasiswa->registrations->isEmpty())
        <p class="text-sm text-slate-500">Belum ada penempatan.</p>
    @else
        <table class="w-full text-sm">
            <thead class="text-left text-xs text-slate-500 border-b">
                <tr>
                    <th class="py-2">Program</th>
                    <th>Gelombang</th>
                    <th>Sekolah</th>
                    <th>Jarak</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
            @foreach($mahasiswa->registrations as $r)
                <tr>
                    <td class="py-2 font-semibold">{{ $r->program }}</td>
                    <td class="text-xs text-slate-600">{{ $r->gelombang ? $r->gelombang->label() : '-' }}</td>
                    <td>{{ $r->school->name }}</td>
                    <td>{{ number_format($r->distance_km, 2) }} km</td>
                    <td>
                        @php $sc2 = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose','cancelled'=>'slate'][$r->status]; @endphp
                        <span class="text-xs px-2 py-1 rounded bg-{{ $sc2 }}-100 text-{{ $sc2 }}-700 capitalize">{{ $r->status }}</span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
