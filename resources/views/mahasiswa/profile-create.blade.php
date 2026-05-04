@extends('layouts.app')
@section('title', 'Lengkapi Pendaftaran')
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="max-w-5xl mx-auto space-y-4">

{{-- ── Header ─────────────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded p-6">
    <h1 class="text-xl font-bold mb-1">Lengkapi Pendaftaran</h1>
    <p class="text-sm text-slate-500">Data ini akan direview admin. Setelah disetujui, sistem akan otomatis menetapkan lokasi berdasarkan domisili dan program yang Anda pilih.</p>
</div>

<form method="POST" action="{{ route('mahasiswa.profile.store') }}" enctype="multipart/form-data"
      class="space-y-4" id="reg-form">
@csrf

{{-- ── Pilih Program ───────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded p-5 space-y-3">
    <h2 class="font-semibold text-slate-700">Pilih Program</h2>
    <p class="text-xs text-slate-500 -mt-1">Pilih satu program yang ingin Anda ikuti. Penempatan lokasi dilakukan otomatis oleh sistem setelah profil disetujui admin.</p>

    @error('program_choice')
        <p class="text-xs text-rose-600">{{ $message }}</p>
    @enderror

    <div class="grid md:grid-cols-3 gap-3">

        {{-- KPM --}}
        <label for="prog-kpm"
               class="relative flex flex-col gap-2 border-2 rounded-lg p-4 cursor-pointer transition-all
                      border-slate-200 hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
            <input type="radio" name="program_choice" id="prog-kpm" value="KPM" class="absolute top-3 right-3 accent-amber-500"
                   @checked(old('program_choice') === 'KPM')>
            <div class="text-2xl">🏘️</div>
            <div class="font-semibold text-slate-800">KPM</div>
            <div class="text-xs font-medium text-amber-700 bg-amber-100 rounded px-2 py-0.5 w-fit">Desa saja</div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kuliah Pengabdian Masyarakat — Anda akan ditempatkan di satu <strong>desa/kelurahan</strong> untuk program pengabdian masyarakat.
            </p>
            <div class="mt-1 text-xs text-slate-500 space-y-1">
                <p>📍 Sistem mencari desa terdekat dari domisili Anda (radius {{ \App\Models\Setting::get('max_radius_km', 10) }} km)</p>
                <p>🏘️ 1 lokasi penempatan (desa)</p>
            </div>
        </label>

        {{-- PPL --}}
        <label for="prog-ppl"
               class="relative flex flex-col gap-2 border-2 rounded-lg p-4 cursor-pointer transition-all
                      border-slate-200 hover:border-sky-400 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50">
            <input type="radio" name="program_choice" id="prog-ppl" value="PPL" class="absolute top-3 right-3 accent-sky-500"
                   @checked(old('program_choice') === 'PPL')>
            <div class="text-2xl">🏫</div>
            <div class="font-semibold text-slate-800">PPL</div>
            <div class="text-xs font-medium text-sky-700 bg-sky-100 rounded px-2 py-0.5 w-fit">Sekolah saja</div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Praktik Pengalaman Lapangan — Anda akan ditempatkan di satu <strong>sekolah</strong> (SD s.d. MA/SMK) untuk praktik mengajar.
            </p>
            <div class="mt-1 text-xs text-slate-500 space-y-1">
                <p>📍 Sistem mencari sekolah terdekat dari domisili Anda (radius {{ \App\Models\Setting::get('max_radius_km', 10) }} km)</p>
                <p>🏫 1 lokasi penempatan (sekolah)</p>
            </div>
        </label>

        {{-- PKPPM --}}
        <label for="prog-pkppm"
               class="relative flex flex-col gap-2 border-2 rounded-lg p-4 cursor-pointer transition-all
                      border-slate-200 hover:border-violet-400 has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50">
            <input type="radio" name="program_choice" id="prog-pkppm" value="PKPPM" class="absolute top-3 right-3 accent-violet-500"
                   @checked(old('program_choice', 'PKPPM') === 'PKPPM')>
            <div class="text-2xl">🏘️🏫</div>
            <div class="font-semibold text-slate-800">PKPPM</div>
            <div class="text-xs font-medium text-violet-700 bg-violet-100 rounded px-2 py-0.5 w-fit">Desa + Sekolah</div>
            <p class="text-xs text-slate-600 leading-relaxed">
                Program KPM + PPL sekaligus — Anda akan ditempatkan di <strong>satu desa</strong> dan <strong>satu sekolah</strong> yang lokasinya saling berdekatan.
            </p>
            <div class="mt-1 text-xs text-slate-500 space-y-1">
                <p>🔗 Sistem mencari pasangan desa KPM + sekolah PPL yang paling dekat satu sama lain</p>
                <p>📍 2 lokasi penempatan (desa + sekolah)</p>
            </div>
        </label>

    </div>

    {{-- Info box PKPPM --}}
    <div id="info-pkppm"
         class="hidden bg-violet-50 border border-violet-200 rounded-lg p-3 text-xs text-violet-800 space-y-1">
        <p class="font-semibold">ℹ️ Cara kerja penempatan PKPPM</p>
        <p>Sistem tidak mencari desa dan sekolah terdekat dari domisili Anda secara terpisah. Sebaliknya, sistem mencari <strong>pasangan</strong> desa KPM + sekolah PPL yang letaknya paling dekat satu sama lain di seluruh database lokasi. Tujuannya agar jarak tempuh Anda antar dua lokasi penempatan sesedikit mungkin.</p>
    </div>
</div>

{{-- ── Data Diri ────────────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded p-5 space-y-4">
    <h2 class="font-semibold text-slate-700">Data Diri</h2>
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">NIM</label>
            <input name="nim" value="{{ old('nim') }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
            @error('nim') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. HP <span class="text-slate-400">(opsional)</span></label>
            <input name="phone" value="{{ old('phone') }}"
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nilai Microteaching</label>
        <select name="microteaching_grade" required class="w-full border border-slate-300 rounded px-3 py-2">
            @foreach(['A','B','C','D','E'] as $g)
                <option value="{{ $g }}" @selected(old('microteaching_grade')===$g)>{{ $g }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ── Domisili + Peta ─────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded p-5 space-y-4">
    <h2 class="font-semibold text-slate-700">Lokasi Domisili</h2>
    <p class="text-xs text-slate-500 -mt-2" id="loc-hint">
        Sistem menggunakan koordinat ini untuk menetapkan lokasi penempatan terdekat dari tempat tinggal Anda.
    </p>

    <div class="flex flex-col lg:flex-row gap-4">
        {{-- input kiri --}}
        <div class="w-full lg:w-2/5 space-y-3">
            <div>
                <label class="block text-sm font-medium mb-1">Alamat Tempat Tinggal</label>
                <textarea name="address" id="address" rows="3" required
                          class="w-full border border-slate-300 rounded px-3 py-2 text-sm">{{ old('address') }}</textarea>
                @error('address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="searchAddress()"
                        class="text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded">
                    🔍 Cari dari Alamat
                </button>
                <button type="button" onclick="useGeo()"
                        class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded">
                    📍 Lokasi Saya Sekarang
                </button>
            </div>
            <div id="geo-results" class="space-y-1"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium mb-1 text-slate-600">Latitude</label>
                    <input name="latitude" id="lat" type="number" step="any" value="{{ old('latitude') }}" required
                           oninput="syncMapFromInputs()"
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm font-mono">
                    @error('latitude') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1 text-slate-600">Longitude</label>
                    <input name="longitude" id="lng" type="number" step="any" value="{{ old('longitude') }}" required
                           oninput="syncMapFromInputs()"
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm font-mono">
                    @error('longitude') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="text-xs text-slate-400">Atau klik langsung pada peta di sebelah kanan untuk menentukan titik domisili.</p>
        </div>

        {{-- peta kanan --}}
        <div class="w-full lg:w-3/5">
            <div id="map" class="rounded border border-slate-200" style="height: 320px;"></div>
        </div>
    </div>
</div>

{{-- ── Upload Berkas ────────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded p-5 space-y-4">
    <div>
        <h2 class="font-semibold text-slate-700">Berkas Persyaratan</h2>
        <p class="text-xs text-slate-500 mt-1">
            Pilih keempat berkas sekaligus atau satu per satu — klik area unggah atau seret file ke dalamnya.
        </p>
    </div>

    {{-- Drop zone utama --}}
    <div id="drop-zone"
         class="border-2 border-dashed border-slate-300 hover:border-indigo-400 rounded-lg p-6 text-center cursor-pointer transition-colors"
         onclick="document.getElementById('bulk-input').click()"
         ondragover="event.preventDefault();this.classList.add('border-indigo-500','bg-indigo-50')"
         ondragleave="this.classList.remove('border-indigo-500','bg-indigo-50')"
         ondrop="handleDrop(event)">
        <div class="text-3xl mb-2">📂</div>
        <p class="text-sm font-medium text-slate-700">Klik di sini atau seret berkas</p>
        <p class="text-xs text-slate-500 mt-1">PDF, JPG, JPEG, PNG — maksimum 4 berkas sekaligus</p>
        <input id="bulk-input" type="file" multiple accept=".pdf,image/*" class="hidden" onchange="handleFiles(this.files)">
    </div>

    {{-- 4 slot berkas --}}
    <div class="grid sm:grid-cols-2 gap-3">
        @php
        $slots = [
            ['key'=>'transkrip',       'label'=>'📋 Transkrip',            'accept'=>'.pdf,image/*', 'hint'=>'PDF atau gambar'],
            ['key'=>'ktm',             'label'=>'🪪 Kartu Tanda Mahasiswa', 'accept'=>'.pdf,image/*', 'hint'=>'PDF atau gambar'],
            ['key'=>'surat_pengantar', 'label'=>'📄 Surat Pengantar',       'accept'=>'.pdf,image/*', 'hint'=>'PDF atau gambar'],
            ['key'=>'pas_foto',        'label'=>'🖼 Pas Foto',              'accept'=>'image/jpeg,image/png', 'hint'=>'JPG atau PNG'],
        ];
        @endphp
        @foreach($slots as $slot)
        <div class="border border-slate-200 rounded-lg p-3 space-y-2" id="slot-{{ $slot['key'] }}">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-slate-700" for="file-{{ $slot['key'] }}">
                    {{ $slot['label'] }} <span class="text-rose-500">*</span>
                </label>
                <span id="badge-{{ $slot['key'] }}"
                      class="hidden text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">✓ Siap</span>
            </div>
            <p class="text-xs text-slate-400">{{ $slot['hint'] }}</p>
            <div id="preview-{{ $slot['key'] }}" class="hidden rounded border border-slate-200 bg-slate-50 text-xs text-slate-600 p-2 flex items-center gap-2">
                <span id="preview-icon-{{ $slot['key'] }}" class="text-lg">📄</span>
                <span id="preview-name-{{ $slot['key'] }}" class="truncate flex-1"></span>
                <button type="button" onclick="clearSlot('{{ $slot['key'] }}')"
                        class="text-rose-500 hover:text-rose-700 shrink-0">✕</button>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('file-{{ $slot['key'] }}').click()"
                        class="text-xs border border-slate-300 hover:border-indigo-400 rounded px-3 py-1.5 text-slate-600 hover:text-indigo-700">
                    Pilih file lain
                </button>
                <input type="file" id="file-{{ $slot['key'] }}" accept="{{ $slot['accept'] }}" class="hidden"
                       onchange="assignFile('{{ $slot['key'] }}', this.files[0])">
            </div>
            <input type="file" name="{{ $slot['key'] }}" id="real-{{ $slot['key'] }}" class="hidden" required>
            @error($slot['key']) <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        @endforeach
    </div>
</div>

{{-- ── Submit ───────────────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded p-4 flex justify-end">
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-8 rounded">
        Kirim untuk Review
    </button>
</div>

</form>
</div>

<script>
// ── Tampilkan info box PKPPM ──────────────────────────────────────────────
function updateProgramInfo() {
    const val = document.querySelector('input[name="program_choice"]:checked')?.value;
    document.getElementById('info-pkppm').classList.toggle('hidden', val !== 'PKPPM');
    const hint = document.getElementById('loc-hint');
    if (val === 'KPM') {
        hint.textContent = 'Sistem menggunakan koordinat ini untuk mencari desa KPM terdekat dari domisili Anda.';
    } else if (val === 'PPL') {
        hint.textContent = 'Sistem menggunakan koordinat ini untuk mencari sekolah PPL terdekat dari domisili Anda.';
    } else {
        hint.textContent = 'Untuk PKPPM, koordinat domisili digunakan sebagai referensi. Sistem akan memilih pasangan desa + sekolah yang paling berdekatan satu sama lain.';
    }
}
document.querySelectorAll('input[name="program_choice"]').forEach(r => r.addEventListener('change', updateProgramInfo));
document.addEventListener('DOMContentLoaded', updateProgramInfo);

// ── Peta ─────────────────────────────────────────────────────────────────
const hasOld = {{ old('latitude') ? 'true' : 'false' }};
const map    = L.map('map').setView([{{ old('latitude', -6.2000) }}, {{ old('longitude', 106.8160) }}], hasOld ? 14 : 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors', maxZoom: 19
}).addTo(map);

const marker = L.marker([{{ old('latitude', -6.2000) }}, {{ old('longitude', 106.8160) }}], { draggable: true }).addTo(map);
if (!hasOld) marker.setOpacity(0);

function setCoords(lat, lng) {
    const la = parseFloat(lat).toFixed(7), ln = parseFloat(lng).toFixed(7);
    document.getElementById('lat').value = la;
    document.getElementById('lng').value = ln;
    marker.setLatLng([la, ln]); marker.setOpacity(1);
    map.setView([la, ln], map.getZoom() < 13 ? 14 : map.getZoom());
}
marker.on('dragend', e => { const ll = e.target.getLatLng(); setCoords(ll.lat, ll.lng); });
map.on('click', e => setCoords(e.latlng.lat, e.latlng.lng));
function syncMapFromInputs() {
    const la = parseFloat(document.getElementById('lat').value);
    const ln = parseFloat(document.getElementById('lng').value);
    if (!isNaN(la) && !isNaN(ln)) { marker.setLatLng([la, ln]); marker.setOpacity(1); map.setView([la, ln]); }
}
function useGeo() {
    if (!navigator.geolocation) return alert('Browser tidak mendukung geolokasi');
    navigator.geolocation.getCurrentPosition(p => setCoords(p.coords.latitude, p.coords.longitude),
        e => alert('Gagal: ' + e.message));
}
const csrf = '{{ csrf_token() }}';
async function searchAddress() {
    const q = document.getElementById('address').value.trim();
    const out = document.getElementById('geo-results');
    if (q.length < 3) { out.innerHTML = '<p class="text-xs text-rose-600">Masukkan alamat minimal 3 karakter.</p>'; return; }
    out.innerHTML = '<p class="text-xs text-slate-500">Mencari…</p>';
    try {
        const r = await fetch(`{{ route('geocode') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin',
        });
        const data = await r.json();
        if (!data.results?.length) { out.innerHTML = '<p class="text-xs text-rose-600">Lokasi tidak ditemukan.</p>'; return; }
        out.innerHTML = data.results.map(res =>
            `<button type="button" data-lat="${res.lat}" data-lng="${res.lon}"
                     class="block w-full text-left text-xs bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded px-2 py-1">
                 ${res.display_name}</button>`).join('');
        out.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
            setCoords(b.dataset.lat, b.dataset.lng);
            out.innerHTML = '<p class="text-xs text-emerald-700">✓ Lokasi dipilih di peta</p>';
        }));
    } catch { out.innerHTML = '<p class="text-xs text-rose-600">Gagal menghubungi layanan geocoding.</p>'; }
}

// ── Upload Berkas ─────────────────────────────────────────────────────────
const slotOrder = ['transkrip','ktm','surat_pengantar','pas_foto'];
const slotFiles = { transkrip: null, ktm: null, surat_pengantar: null, pas_foto: null };

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.remove('border-indigo-500','bg-indigo-50');
    handleFiles(e.dataTransfer.files);
}
function handleFiles(fileList) {
    let idx = 0;
    Array.from(fileList).forEach(f => {
        while (idx < slotOrder.length && slotFiles[slotOrder[idx]]) idx++;
        if (idx < slotOrder.length) { assignFile(slotOrder[idx], f); idx++; }
    });
}
function assignFile(key, file) {
    if (!file) return;
    slotFiles[key] = file;
    try { const dt = new DataTransfer(); dt.items.add(file); document.getElementById('real-'+key).files = dt.files; } catch {}
    const isImg = file.type.startsWith('image/');
    document.getElementById('preview-icon-'+key).textContent = isImg ? '🖼' : '📄';
    document.getElementById('preview-name-'+key).textContent = file.name;
    document.getElementById('preview-'+key).classList.remove('hidden');
    document.getElementById('badge-'+key).classList.remove('hidden');
    document.getElementById('slot-'+key).classList.add('border-emerald-300');
}
function clearSlot(key) {
    slotFiles[key] = null;
    document.getElementById('real-'+key).value = '';
    document.getElementById('preview-'+key).classList.add('hidden');
    document.getElementById('badge-'+key).classList.add('hidden');
    document.getElementById('slot-'+key).classList.remove('border-emerald-300');
}
slotOrder.forEach(key => {
    document.getElementById('file-'+key)?.addEventListener('change', function() {
        if (this.files[0]) assignFile(key, this.files[0]);
    });
});
document.addEventListener('DOMContentLoaded', () => map.invalidateSize());
</script>
@endsection
