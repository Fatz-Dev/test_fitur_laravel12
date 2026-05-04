@extends('layouts.app')
@section('title', $school->exists ? 'Edit Lokasi' : 'Tambah Lokasi')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="mb-lg">
    <a href="{{ route('admin.schools.index') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors font-label-md">
        <i class="ti ti-arrow-left text-[18px]"></i> Kembali
    </a>
    <h2 class="font-h2 text-h2 text-primary mt-sm">{{ $school->exists ? 'Edit Lokasi' : 'Tambah Lokasi' }}</h2>
    <p class="font-body-sm text-on-surface-variant">KPM &rarr; Desa &nbsp;|&nbsp; PPL &rarr; Sekolah</p>
</div>

<div class="flex flex-col lg:flex-row gap-gutter">
    {{-- Form --}}
    <form method="POST"
          action="{{ $school->exists ? route('admin.schools.update', $school) : route('admin.schools.store') }}"
          class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm w-full lg:w-1/2 space-y-md"
          id="lokasi-form">
        @csrf
        @if($school->exists) @method('PUT') @endif

        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">Program</label>
            <select name="program" id="prog-select"
                    class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                    onchange="updateLabels()">
                @foreach(['KPM' => 'KPM — Desa / Kelurahan', 'PPL' => 'PPL — Sekolah (SD s.d. MA/SMK)'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('program', $school->program ?? 'KPM') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            @error('program') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-3 gap-md">
            <div class="col-span-2 space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block" id="label-nama">Nama Lokasi</label>
                <input name="name" value="{{ old('name', $school->name) }}" required id="input-nama"
                       placeholder="Nama desa atau sekolah"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"/>
                @error('name') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
            </div>
            <div id="wrap-jenjang" class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Jenjang</label>
                <select name="jenjang" id="sel-jenjang"
                        class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all">
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

        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">Alamat / Lokasi</label>
            <textarea name="address" id="address" rows="2" required
                      placeholder="Alamat lengkap"
                      class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all resize-none">{{ old('address', $school->address) }}</textarea>
            @error('address') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-md">
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Latitude</label>
                <input name="latitude" id="lat" type="number" step="any"
                       value="{{ old('latitude', $school->latitude) }}" required
                       oninput="syncMapFromInputs()"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all font-mono"/>
            </div>
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Longitude</label>
                <input name="longitude" id="lng" type="number" step="any"
                       value="{{ old('longitude', $school->longitude) }}" required
                       oninput="syncMapFromInputs()"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all font-mono"/>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="useGeo()"
                    class="flex items-center gap-1 text-[12px] bg-secondary/10 text-secondary hover:bg-secondary/20 px-md py-xs rounded-lg font-medium transition-colors">
                <i class="ti ti-current-location text-[16px]"></i> Lokasi GPS Saya
            </button>
            <button type="button" onclick="searchAddress()"
                    class="flex items-center gap-1 text-[12px] bg-primary/10 text-primary hover:bg-primary/20 px-md py-xs rounded-lg font-medium transition-colors">
                <i class="ti ti-search text-[16px]"></i> Cari via Alamat
            </button>
        </div>
        <div id="geo-results" class="space-y-1"></div>

        <div class="grid grid-cols-2 gap-md">
            <div id="wrap-kuota-kpm" class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Kuota KPM</label>
                <input name="kuota_kpm" type="number" min="0"
                       value="{{ old('kuota_kpm', $school->kuota_kpm ?? 0) }}" required
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all"/>
            </div>
            <div id="wrap-kuota-ppl" class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Kuota PPL</label>
                <input name="kuota_ppl" type="number" min="0"
                       value="{{ old('kuota_ppl', $school->kuota_ppl ?? 0) }}" required
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all"/>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-md">
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Kontak Person</label>
                <input name="contact_person" value="{{ old('contact_person', $school->contact_person) }}"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all"/>
            </div>
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">No. Telp</label>
                <input name="phone" value="{{ old('phone', $school->phone) }}"
                       class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary outline-none transition-all"/>
            </div>
        </div>

        <label class="flex items-center gap-sm cursor-pointer">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $school->is_active ?? true))
                   class="rounded border-outline-variant text-secondary focus:ring-secondary">
            <span class="font-body-sm text-on-surface">Aktif (dapat digunakan untuk penempatan)</span>
        </label>

        <div class="flex justify-end pt-sm">
            <button class="bg-primary hover:bg-primary-container text-white font-label-md py-2 px-lg rounded-lg transition-colors flex items-center gap-2">
                <i class="ti ti-device-floppy text-[18px]"></i>
                Simpan
            </button>
        </div>
    </form>

    {{-- Peta --}}
    <div class="w-full lg:w-1/2 flex flex-col gap-md">
        <div class="bg-surface-container-low border border-slate-200 rounded-xl p-sm">
            <div class="flex items-center gap-2">
                <i class="ti ti-map text-primary text-[18px]"></i>
                <p class="font-label-md text-on-surface text-sm">Peta Lokasi</p>
            </div>
            <p class="text-[12px] text-on-surface-variant mt-1">Klik pada peta untuk menentukan koordinat, atau geser marker.</p>
        </div>
        <div id="map" class="rounded-xl border border-slate-200 w-full shadow-sm" style="height: 500px; min-height: 400px;"></div>
        <p class="text-[12px] text-on-surface-variant text-center">Tile peta: OpenStreetMap (gratis, tanpa API key)</p>
    </div>
</div>

@push('scripts')
<script>
const initLat = {{ old('latitude', $school->latitude ?? -6.2000) }};
const initLng = {{ old('longitude', $school->longitude ?? 106.8160) }};
const hasCoord = {{ ($school->latitude && $school->longitude) || old('latitude') ? 'true' : 'false' }};
const map = L.map('map').setView([initLat, initLng], hasCoord ? 14 : 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom: 19
}).addTo(map);
const marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
function setCoords(lat, lng) {
    const la = parseFloat(lat).toFixed(7), ln = parseFloat(lng).toFixed(7);
    document.getElementById('lat').value = la;
    document.getElementById('lng').value = ln;
    marker.setLatLng([la, ln]);
    map.setView([la, ln], map.getZoom() < 13 ? 14 : map.getZoom());
}
marker.on('dragend', e => { const ll = e.target.getLatLng(); setCoords(ll.lat, ll.lng); });
map.on('click', e => setCoords(e.latlng.lat, e.latlng.lng));
function syncMapFromInputs() {
    const la = parseFloat(document.getElementById('lat').value);
    const ln = parseFloat(document.getElementById('lng').value);
    if (!isNaN(la) && !isNaN(ln)) { marker.setLatLng([la, ln]); map.setView([la, ln]); }
}
function updateLabels() {
    const prog = document.getElementById('prog-select').value;
    const namaEl = document.getElementById('label-nama');
    const inputEl = document.getElementById('input-nama');
    const kuotaKpm = document.getElementById('wrap-kuota-kpm');
    const kuotaPpl = document.getElementById('wrap-kuota-ppl');
    const grpSekolah = document.getElementById('grp-sekolah');
    const grpDesa = document.getElementById('grp-desa');
    if (prog === 'KPM') {
        namaEl.textContent = 'Nama Desa'; inputEl.placeholder = 'Nama desa / kelurahan';
        grpDesa.style.display = ''; grpSekolah.style.display = 'none';
        kuotaKpm.style.display = ''; kuotaPpl.style.display = 'none';
    } else {
        namaEl.textContent = 'Nama Sekolah'; inputEl.placeholder = 'Nama sekolah';
        grpDesa.style.display = 'none'; grpSekolah.style.display = '';
        kuotaKpm.style.display = 'none'; kuotaPpl.style.display = '';
    }
}
document.addEventListener('DOMContentLoaded', () => { updateLabels(); map.invalidateSize(); });
function useGeo() {
    if (!navigator.geolocation) return alert('Browser tidak mendukung geolokasi');
    navigator.geolocation.getCurrentPosition(p => setCoords(p.coords.latitude, p.coords.longitude),
        e => alert('Gagal: ' + e.message));
}
async function searchAddress() {
    const q = document.getElementById('address').value.trim();
    const out = document.getElementById('geo-results');
    if (q.length < 3) { out.innerHTML = '<p class="text-[12px] text-error">Masukkan alamat minimal 3 karakter.</p>'; return; }
    out.innerHTML = '<p class="text-[12px] text-on-surface-variant">Mencari...</p>';
    try {
        const r = await fetch(`{{ route('geocode') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, credentials: 'same-origin',
        });
        const data = await r.json();
        if (!data.results?.length) { out.innerHTML = '<p class="text-[12px] text-error">Lokasi tidak ditemukan.</p>'; return; }
        out.innerHTML = data.results.map(res =>
            `<button type="button" data-lat="${res.lat}" data-lng="${res.lon}"
                     class="block w-full text-left text-[12px] bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg px-md py-xs transition-colors">
                 ${res.display_name}</button>`).join('');
        out.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
            setCoords(b.dataset.lat, b.dataset.lng);
            out.innerHTML = `<p class="text-[12px] text-secondary font-medium">✓ Lokasi dipilih di peta</p>`;
        }));
    } catch (e) {
        out.innerHTML = '<p class="text-[12px] text-error">Gagal menghubungi layanan geocoding.</p>';
    }
}
</script>
@endpush
@endsection
