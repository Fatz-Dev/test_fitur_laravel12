@extends('layouts.app')
@section('title', $school->exists ? 'Edit Lokasi' : 'Tambah Lokasi')
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<a href="{{ route('admin.schools.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Kembali</a>
<h1 class="text-2xl font-bold mt-2 mb-1">{{ $school->exists ? 'Edit Lokasi' : 'Tambah Lokasi' }}</h1>
<p class="text-xs text-slate-500 mb-4">KPM &rarr; Desa &nbsp;|&nbsp; PPL &rarr; Sekolah</p>

<div class="flex flex-col lg:flex-row gap-4">

{{-- ── Kolom Form ─────────────────────────────────────────────────────────── --}}
<form method="POST"
      action="{{ $school->exists ? route('admin.schools.update', $school) : route('admin.schools.store') }}"
      class="bg-white border border-slate-200 rounded p-6 w-full lg:w-1/2 space-y-4"
      id="lokasi-form">
    @csrf
    @if($school->exists) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium mb-1">Program</label>
        <select name="program" id="prog-select" class="w-full border border-slate-300 rounded px-3 py-2"
                onchange="updateLabels()">
            @foreach(['BOTH'=>'KPM (Desa) & PPL (Sekolah)','KPM'=>'KPM saja (Desa)','PPL'=>'PPL saja (Sekolah)'] as $v=>$l)
                <option value="{{ $v }}" @selected(old('program', $school->program ?? 'BOTH')===$v)>{{ $l }}</option>
            @endforeach
        </select>
        @error('program') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium mb-1" id="label-nama">Nama Lokasi</label>
            <input name="name" value="{{ old('name', $school->name) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2"
                   id="input-nama" placeholder="Nama desa atau sekolah">
            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div id="wrap-jenjang">
            <label class="block text-sm font-medium mb-1">Jenjang</label>
            <select name="jenjang" id="sel-jenjang" class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="">— Pilih —</option>
                <optgroup label="Sekolah (PPL)" id="grp-sekolah">
                    @foreach(['SD','SMP','SMA','SMK','MI','MTs','MA'] as $j)
                        <option value="{{ $j }}" @selected(old('jenjang', $school->jenjang)===$j)>{{ $j }}</option>
                    @endforeach
                </optgroup>
                <optgroup label="Desa (KPM)" id="grp-desa">
                    @foreach(['Desa','Kelurahan','Kecamatan'] as $j)
                        <option value="{{ $j }}" @selected(old('jenjang', $school->jenjang)===$j)>{{ $j }}</option>
                    @endforeach
                </optgroup>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Alamat / Lokasi</label>
        <textarea name="address" id="address" rows="2" required
                  class="w-full border border-slate-300 rounded px-3 py-2"
                  placeholder="Alamat lengkap">{{ old('address', $school->address) }}</textarea>
        @error('address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Koordinat --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Latitude</label>
            <input name="latitude" id="lat" type="number" step="any"
                   value="{{ old('latitude', $school->latitude) }}" required
                   oninput="syncMapFromInputs()"
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Longitude</label>
            <input name="longitude" id="lng" type="number" step="any"
                   value="{{ old('longitude', $school->longitude) }}" required
                   oninput="syncMapFromInputs()"
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button" onclick="useGeo()"
                class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded">
            📍 Lokasi GPS Saya
        </button>
        <button type="button" onclick="searchAddress()"
                class="text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded">
            🔍 Cari via Alamat
        </button>
    </div>
    <div id="geo-results" class="space-y-1"></div>

    <div class="grid grid-cols-2 gap-4">
        <div id="wrap-kuota-kpm">
            <label class="block text-sm font-medium mb-1">Kuota KPM (Desa)</label>
            <input name="kuota_kpm" type="number" min="0"
                   value="{{ old('kuota_kpm', $school->kuota_kpm ?? 0) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div id="wrap-kuota-ppl">
            <label class="block text-sm font-medium mb-1">Kuota PPL (Sekolah)</label>
            <input name="kuota_ppl" type="number" min="0"
                   value="{{ old('kuota_ppl', $school->kuota_ppl ?? 0) }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Kontak Person</label>
            <input name="contact_person" value="{{ old('contact_person', $school->contact_person) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. Telp</label>
            <input name="phone" value="{{ old('phone', $school->phone) }}"
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $school->is_active ?? true))>
        Aktif (dapat digunakan untuk penempatan)
    </label>

    <div class="flex justify-end pt-2">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded">
            Simpan
        </button>
    </div>
</form>

{{-- ── Kolom Peta ──────────────────────────────────────────────────────────── --}}
<div class="w-full lg:w-1/2 flex flex-col gap-2">
    <div class="bg-white border border-slate-200 rounded p-3">
        <p class="text-xs text-slate-600 font-medium mb-1">🗺 Peta Lokasi</p>
        <p class="text-xs text-slate-500">Klik pada peta untuk menentukan koordinat, atau geser marker.</p>
    </div>
    <div id="map" class="rounded border border-slate-200 w-full" style="height: 500px; min-height:400px;"></div>
    <p class="text-xs text-slate-400 text-center">Tile peta: OpenStreetMap (gratis, tanpa API key)</p>
</div>

</div>

<script>
const initLat = {{ old('latitude', $school->latitude ?? -6.2000) }};
const initLng = {{ old('longitude', $school->longitude ?? 106.8160) }};
const hasCoord = {{ ($school->latitude && $school->longitude) || old('latitude') ? 'true' : 'false' }};

const map = L.map('map').setView([initLat, initLng], hasCoord ? 14 : 10);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(map);

const marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);

function setCoords(lat, lng) {
    const la = parseFloat(lat).toFixed(7);
    const ln = parseFloat(lng).toFixed(7);
    document.getElementById('lat').value = la;
    document.getElementById('lng').value = ln;
    marker.setLatLng([la, ln]);
    map.setView([la, ln], map.getZoom() < 13 ? 14 : map.getZoom());
}

marker.on('dragend', e => {
    const ll = e.target.getLatLng();
    setCoords(ll.lat, ll.lng);
});

map.on('click', e => setCoords(e.latlng.lat, e.latlng.lng));

function syncMapFromInputs() {
    const la = parseFloat(document.getElementById('lat').value);
    const ln = parseFloat(document.getElementById('lng').value);
    if (!isNaN(la) && !isNaN(ln)) {
        marker.setLatLng([la, ln]);
        map.setView([la, ln]);
    }
}

function updateLabels() {
    const prog = document.getElementById('prog-select').value;
    const namaEl   = document.getElementById('label-nama');
    const inputEl  = document.getElementById('input-nama');
    const kuotaKpm = document.getElementById('wrap-kuota-kpm');
    const kuotaPpl = document.getElementById('wrap-kuota-ppl');
    const grpSekolah = document.getElementById('grp-sekolah');
    const grpDesa    = document.getElementById('grp-desa');

    if (prog === 'KPM') {
        namaEl.textContent = 'Nama Desa'; inputEl.placeholder = 'Nama desa / kelurahan';
        grpDesa.style.display = ''; grpSekolah.style.display = 'none';
        kuotaKpm.style.display = ''; kuotaPpl.style.display = 'none';
    } else if (prog === 'PPL') {
        namaEl.textContent = 'Nama Sekolah'; inputEl.placeholder = 'Nama sekolah';
        grpDesa.style.display = 'none'; grpSekolah.style.display = '';
        kuotaKpm.style.display = 'none'; kuotaPpl.style.display = '';
    } else {
        namaEl.textContent = 'Nama Lokasi'; inputEl.placeholder = 'Nama desa atau sekolah';
        grpDesa.style.display = ''; grpSekolah.style.display = '';
        kuotaKpm.style.display = ''; kuotaPpl.style.display = '';
    }
}
document.addEventListener('DOMContentLoaded', () => { updateLabels(); map.invalidateSize(); });

function useGeo() {
    if (!navigator.geolocation) return alert('Browser tidak mendukung geolokasi');
    navigator.geolocation.getCurrentPosition(p => setCoords(p.coords.latitude, p.coords.longitude),
        e => alert('Gagal mendapatkan lokasi: ' + e.message));
}

async function searchAddress() {
    const q = document.getElementById('address').value.trim();
    const out = document.getElementById('geo-results');
    if (q.length < 3) { out.innerHTML = '<p class="text-xs text-rose-600">Masukkan alamat minimal 3 karakter.</p>'; return; }
    out.innerHTML = '<p class="text-xs text-slate-500">Mencari...</p>';
    try {
        const r = await fetch(`{{ route('geocode') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            credentials: 'same-origin',
        });
        const data = await r.json();
        if (!data.results?.length) { out.innerHTML = '<p class="text-xs text-rose-600">Lokasi tidak ditemukan.</p>'; return; }
        out.innerHTML = data.results.map(res => `
            <button type="button" data-lat="${res.lat}" data-lng="${res.lon}"
                    class="block w-full text-left text-xs bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded px-2 py-1">
                ${res.display_name}</button>`).join('');
        out.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
            setCoords(b.dataset.lat, b.dataset.lng);
            out.innerHTML = `<p class="text-xs text-emerald-700">✓ Lokasi dipilih di peta</p>`;
        }));
    } catch (e) {
        out.innerHTML = '<p class="text-xs text-rose-600">Gagal menghubungi layanan geocoding.</p>';
    }
}
</script>
@endsection
