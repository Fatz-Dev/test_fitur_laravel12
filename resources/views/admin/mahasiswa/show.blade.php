@extends('layouts.app')
@section('title', 'Detail Mahasiswa')
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<a href="{{ route('admin.mahasiswa.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
<h1 class="text-2xl font-bold mt-2 mb-4">{{ $mahasiswa->user->name }}</h1>

<div class="grid md:grid-cols-3 gap-4 mb-4">
    {{-- ── Info Profil ──────────────────────────────────────────────────── --}}
    <div class="bg-white border border-slate-200 rounded p-4 md:col-span-2 space-y-2 text-sm">
        <p><span class="text-slate-500">Email:</span> {{ $mahasiswa->user->email }}</p>
        <p><span class="text-slate-500">NIM:</span> {{ $mahasiswa->nim }}</p>
        <p><span class="text-slate-500">No. HP:</span> {{ $mahasiswa->phone ?? '-' }}</p>
        <p><span class="text-slate-500">Alamat:</span> {{ $mahasiswa->address }}</p>
        <p class="text-slate-500 text-xs">Koordinat: {{ $mahasiswa->latitude }}, {{ $mahasiswa->longitude }}</p>
        <p><span class="text-slate-500">Nilai Microteaching:</span> <strong>{{ $mahasiswa->microteaching_grade }}</strong></p>
        <div>
            <p class="text-slate-500 mb-1">Berkas:</p>
            <ul class="list-none space-y-1">
                @foreach([
                    ['Transkrip', $mahasiswa->transkrip_path],
                    ['Kartu Tanda Mahasiswa', $mahasiswa->ktm_path],
                    ['Surat Pengantar', $mahasiswa->surat_pengantar_path],
                    ['Pas Foto', $mahasiswa->pas_foto_path],
                ] as [$label, $path])
                    @if($path)
                        <li>
                            <a target="_blank" href="{{ asset('storage/'.$path) }}"
                               class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline">
                                📄 {{ $label }}
                            </a>
                        </li>
                    @else
                        <li class="text-xs text-slate-400">{{ $label }} — belum diunggah</li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ── Panel Aksi ───────────────────────────────────────────────────── --}}
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

{{-- ── Peta Domisili & Penempatan ───────────────────────────────────────── --}}
@if($mahasiswa->latitude && $mahasiswa->longitude)
<div class="bg-white border border-slate-200 rounded p-4 mb-4">
    <h2 class="font-semibold mb-1">Peta Lokasi</h2>
    <div class="flex flex-wrap gap-4 text-xs text-slate-600 mb-3">
        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span> Domisili Mahasiswa</span>
        @if($mahasiswa->registrations->where('program','KPM')->first())
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-amber-500"></span> Desa KPM</span>
        @endif
        @if($mahasiswa->registrations->where('program','PPL')->first())
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-sky-500"></span> Sekolah PPL</span>
        @endif
    </div>
    <div id="map" class="rounded border border-slate-200" style="height: 380px;"></div>
</div>
@endif

{{-- ── Riwayat Penempatan ───────────────────────────────────────────────── --}}
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
                    <th>Lokasi (Desa/Sekolah)</th>
                    <th>Jarak</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
            @foreach($mahasiswa->registrations as $r)
                <tr>
                    <td class="py-2 font-semibold">{{ $r->program }}</td>
                    <td class="text-xs text-slate-600">{{ $r->gelombang ? $r->gelombang->label() : '-' }}</td>
                    <td>
                        <span class="font-medium">{{ $r->school->name }}</span>
                        <span class="text-xs text-slate-400 ml-1">({{ $r->school->locationType() }})</span>
                    </td>
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

@if($mahasiswa->latitude && $mahasiswa->longitude)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const homeLat = {{ $mahasiswa->latitude }};
    const homeLng = {{ $mahasiswa->longitude }};

    const map = L.map('map').setView([homeLat, homeLng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);

    // Ikon warna berbeda
    function colorIcon(color) {
        const colors = {
            blue:  '#3B82F6', amber: '#F59E0B', sky: '#0EA5E9'
        };
        const c = colors[color] || '#6366F1';
        return L.divIcon({
            className: '',
            html: `<div style="width:14px;height:14px;border-radius:50%;background:${c};border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>`,
            iconSize: [14, 14], iconAnchor: [7, 7]
        });
    }

    const bounds = [];

    // Domisili
    const homeMarker = L.marker([homeLat, homeLng], { icon: colorIcon('blue') })
        .addTo(map)
        .bindPopup(`<b>Domisili</b><br>${{!! json_encode($mahasiswa->address) !!}}`);
    bounds.push([homeLat, homeLng]);

    @foreach($mahasiswa->registrations->load('school') as $r)
    @if($r->school)
    (function() {
        const prog   = '{{ $r->program }}';
        const sLat   = {{ $r->school->latitude }};
        const sLng   = {{ $r->school->longitude }};
        const sName  = {!! json_encode($r->school->name) !!};
        const sType  = {!! json_encode($r->school->locationType()) !!};
        const dist   = '{{ number_format($r->distance_km, 2) }} km';
        const status = '{{ $r->status }}';
        const color  = prog === 'KPM' ? 'amber' : 'sky';

        L.marker([sLat, sLng], { icon: colorIcon(color) })
            .addTo(map)
            .bindPopup(`<b>${prog} — ${sType}</b><br>${sName}<br><small>Jarak: ${dist} | ${status}</small>`);

        // Garis dari domisili ke lokasi
        L.polyline([[homeLat, homeLng], [sLat, sLng]], {
            color: prog === 'KPM' ? '#F59E0B' : '#0EA5E9',
            weight: 2, dashArray: '5,5', opacity: 0.7
        }).addTo(map);

        bounds.push([sLat, sLng]);
    })();
    @endif
    @endforeach

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [40, 40] });
    }
});
</script>
@endif
@endsection
