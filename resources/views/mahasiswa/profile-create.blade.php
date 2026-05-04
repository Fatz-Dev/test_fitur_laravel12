@extends('layouts.app')
@section('title', 'Lengkapi Pendaftaran')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="mb-lg">
    <h2 class="font-h2 text-h2 text-primary">Lengkapi Pendaftaran</h2>
    <p class="font-body-sm text-on-surface-variant">Data ini akan direview admin. Setelah disetujui, sistem akan otomatis menetapkan lokasi penempatan.</p>
</div>

<form method="POST" action="{{ route('mahasiswa.profile.store') }}" enctype="multipart/form-data"
      class="space-y-lg max-w-4xl" id="reg-form">
@csrf

{{-- Pilih Program --}}
<div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm">
    <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider text-[11px] mb-md">Pilih Program</h3>
    <p class="font-body-sm text-on-surface-variant mb-md">Pilih satu program yang ingin Anda ikuti. Penempatan lokasi dilakukan otomatis oleh sistem setelah profil disetujui admin.</p>

    @error('program_choice')
        <div class="mb-md px-md py-sm rounded-lg bg-error/10 border border-error/20 text-[12px] text-error">{{ $message }}</div>
    @enderror

    <div class="grid md:grid-cols-3 gap-md">
        <label for="prog-kpm"
               class="relative flex flex-col gap-2 border-2 rounded-xl p-lg cursor-pointer transition-all
                      border-slate-200 hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
            <input type="radio" name="program_choice" id="prog-kpm" value="KPM"
                   class="absolute top-3 right-3 accent-amber-500"
                   @checked(old('program_choice') === 'KPM')>
            <i class="ti ti-building-community text-amber-600 text-[28px]"></i>
            <div class="font-label-md text-on-surface">KPM</div>
            <div class="text-[11px] font-bold px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full w-fit">Desa saja</div>
            <p class="font-body-sm text-on-surface-variant text-[13px]">
                Kuliah Pengabdian Masyarakat — ditempatkan di satu <strong>desa/kelurahan</strong>.
            </p>
            <div class="text-[12px] text-on-surface-variant space-y-1">
                <p class="flex items-center gap-1"><i class="ti ti-navigation text-[14px]"></i> Radius {{ \App\Models\Setting::get('max_radius_km', 10) }} km dari domisili</p>
                <p class="flex items-center gap-1"><i class="ti ti-home text-[14px]"></i> 1 lokasi penempatan (desa)</p>
            </div>
        </label>

        <label for="prog-ppl"
               class="relative flex flex-col gap-2 border-2 rounded-xl p-lg cursor-pointer transition-all
                      border-slate-200 hover:border-blue-400 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
            <input type="radio" name="program_choice" id="prog-ppl" value="PPL"
                   class="absolute top-3 right-3 accent-blue-500"
                   @checked(old('program_choice') === 'PPL')>
            <i class="ti ti-school text-blue-600 text-[28px]"></i>
            <div class="font-label-md text-on-surface">PPL</div>
            <div class="text-[11px] font-bold px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full w-fit">Sekolah saja</div>
            <p class="font-body-sm text-on-surface-variant text-[13px]">
                Praktik Pengalaman Lapangan — ditempatkan di satu <strong>sekolah</strong> (SD s.d. MA/SMK).
            </p>
            <div class="text-[12px] text-on-surface-variant space-y-1">
                <p class="flex items-center gap-1"><i class="ti ti-navigation text-[14px]"></i> Radius {{ \App\Models\Setting::get('max_radius_km', 10) }} km dari domisili</p>
                <p class="flex items-center gap-1"><i class="ti ti-school text-[14px]"></i> 1 lokasi penempatan (sekolah)</p>
            </div>
        </label>

        <label for="prog-pkppm"
               class="relative flex flex-col gap-2 border-2 rounded-xl p-lg cursor-pointer transition-all
                      border-slate-200 hover:border-violet-400 has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50">
            <input type="radio" name="program_choice" id="prog-pkppm" value="PKPPM"
                   class="absolute top-3 right-3 accent-violet-500"
                   @checked(old('program_choice', 'PKPPM') === 'PKPPM')>
            <i class="ti ti-topology-star-3 text-violet-600 text-[28px]"></i>
            <div class="font-label-md text-on-surface">PKPPM</div>
            <div class="text-[11px] font-bold px-2 py-0.5 bg-violet-100 text-violet-700 rounded-full w-fit">Desa + Sekolah</div>
            <p class="font-body-sm text-on-surface-variant text-[13px]">
                KPM + PPL sekaligus — ditempatkan di <strong>satu desa</strong> dan <strong>satu sekolah</strong> yang berdekatan.
            </p>
            <div class="text-[12px] text-on-surface-variant space-y-1">
                <p class="flex items-center gap-1"><i class="ti ti-arrows-join text-[14px]"></i> Pasangan desa + sekolah terdekat</p>
                <p class="flex items-center gap-1"><i class="ti ti-layers-intersect text-[14px]"></i> 2 lokasi penempatan</p>
            </div>
        </label>
    </div>

    <div id="info-pkppm" class="hidden mt-md bg-violet-50 border border-violet-200 rounded-xl p-md">
        <div class="flex items-start gap-sm">
            <i class="ti ti-info-circle text-violet-600 text-[18px]"></i>
            <div>
                <p class="font-label-md text-violet-800 text-sm mb-1">Cara Kerja Penempatan PKPPM</p>
                <p class="text-[12px] text-violet-700">Sistem tidak mencari desa dan sekolah terdekat dari domisili Anda secara terpisah. Sebaliknya, sistem mencari <strong>pasangan</strong> desa + sekolah yang letaknya paling dekat satu sama lain. Tujuannya agar jarak tempuh antar dua lokasi penempatan sesedikit mungkin.</p>
            </div>
        </div>
    </div>
</div>

{{-- Data Diri --}}
<div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm space-y-md">
    <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">Data Diri</h3>
    <div class="grid md:grid-cols-2 gap-md">
        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">NIM</label>
            <input name="nim" value="{{ old('nim') }}" required
                   class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"/>
            @error('nim') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="space-y-xs">
            <label class="font-label-md text-label-md text-on-surface block">No. HP <span class="text-outline font-normal">(opsional)</span></label>
            <input name="phone" value="{{ old('phone') }}"
                   class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"/>
        </div>
    </div>
    <div class="space-y-xs">
        <label class="font-label-md text-label-md text-on-surface block">Nilai Microteaching</label>
        <select name="microteaching_grade" required
                class="w-full border border-outline-variant rounded-lg px-md py-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all">
            @foreach(['A','B','C','D','E'] as $g)
                <option value="{{ $g }}" @selected(old('microteaching_grade')===$g)>{{ $g }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Domisili + Peta --}}
<div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm space-y-md">
    <div>
        <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">Lokasi Domisili</h3>
        <p class="font-body-sm text-on-surface-variant mt-1" id="loc-hint">
            Sistem menggunakan koordinat ini untuk menetapkan lokasi penempatan terdekat dari tempat tinggal Anda.
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-lg">
        <div class="w-full lg:w-2/5 space-y-md">
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block">Alamat Tempat Tinggal</label>
                <textarea name="address" id="address" rows="3" required
                          class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none">{{ old('address') }}</textarea>
                @error('address') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="searchAddress()"
                        class="flex items-center gap-1 text-[12px] bg-primary/10 text-primary hover:bg-primary/20 px-md py-xs rounded-lg font-medium transition-colors">
                    <i class="ti ti-search text-[16px]"></i> Cari dari Alamat
                </button>
                <button type="button" onclick="useGeo()"
                        class="flex items-center gap-1 text-[12px] bg-secondary/10 text-secondary hover:bg-secondary/20 px-md py-xs rounded-lg font-medium transition-colors">
                    <i class="ti ti-current-location text-[16px]"></i> Lokasi Saya
                </button>
            </div>
            <div id="geo-results" class="space-y-1"></div>
            <div class="grid grid-cols-2 gap-md">
                <div class="space-y-xs">
                    <label class="font-label-sm text-on-surface-variant block">Latitude</label>
                    <input name="latitude" id="lat" type="number" step="any" value="{{ old('latitude') }}" required
                           oninput="syncMapFromInputs()"
                           class="w-full border border-outline-variant rounded-lg px-md py-xs text-sm font-mono focus:ring-2 focus:ring-secondary outline-none transition-all"/>
                    @error('latitude') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-xs">
                    <label class="font-label-sm text-on-surface-variant block">Longitude</label>
                    <input name="longitude" id="lng" type="number" step="any" value="{{ old('longitude') }}" required
                           oninput="syncMapFromInputs()"
                           class="w-full border border-outline-variant rounded-lg px-md py-xs text-sm font-mono focus:ring-2 focus:ring-secondary outline-none transition-all"/>
                    @error('longitude') <p class="text-[12px] text-error mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="text-[12px] text-outline">Atau klik langsung pada peta untuk menentukan titik domisili.</p>
        </div>

        <div class="w-full lg:w-3/5">
            <div id="map" class="rounded-xl border border-slate-200" style="height: 320px;"></div>
        </div>
    </div>
</div>

{{-- Upload Berkas --}}
<div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm space-y-md">
    <div>
        <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">Berkas Persyaratan</h3>
        <p class="font-body-sm text-on-surface-variant mt-1">Pilih keempat berkas sekaligus atau satu per satu.</p>
    </div>

    <div id="drop-zone"
         class="border-2 border-dashed border-outline-variant hover:border-secondary rounded-xl p-xl text-center cursor-pointer transition-colors"
         onclick="document.getElementById('bulk-input').click()"
         ondragover="event.preventDefault();this.classList.add('border-secondary','bg-secondary/5')"
         ondragleave="this.classList.remove('border-secondary','bg-secondary/5')"
         ondrop="handleDrop(event)">
        <i class="ti ti-folder-open text-[36px] text-outline block mb-2"></i>
        <p class="font-label-md text-on-surface text-sm">Klik di sini atau seret berkas</p>
        <p class="font-body-sm text-on-surface-variant text-[12px] mt-1">PDF, JPG, JPEG, PNG — maksimum 4 berkas</p>
        <input id="bulk-input" type="file" multiple accept=".pdf,image/*" class="hidden" onchange="handleFiles(this.files)">
    </div>

    <div class="grid sm:grid-cols-2 gap-md">
        @php
        $slots = [
            ['key'=>'transkrip',       'label'=>'Transkrip',            'icon'=>'ti-file-description', 'accept'=>'.pdf,image/*', 'hint'=>'PDF atau gambar'],
            ['key'=>'ktm',             'label'=>'Kartu Tanda Mahasiswa', 'icon'=>'ti-id-badge',         'accept'=>'.pdf,image/*', 'hint'=>'PDF atau gambar'],
            ['key'=>'surat_pengantar', 'label'=>'Surat Pengantar',       'icon'=>'ti-mail',             'accept'=>'.pdf,image/*', 'hint'=>'PDF atau gambar'],
            ['key'=>'pas_foto',        'label'=>'Pas Foto',              'icon'=>'ti-user',             'accept'=>'image/jpeg,image/png', 'hint'=>'JPG atau PNG'],
        ];
        @endphp
        @foreach($slots as $slot)
        <div class="border border-outline-variant rounded-xl p-md space-y-sm transition-colors" id="slot-{{ $slot['key'] }}">
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 font-label-md text-on-surface text-sm cursor-pointer" for="file-{{ $slot['key'] }}">
                    <i class="ti {{ $slot['icon'] }} text-on-surface-variant text-[18px]"></i>
                    {{ $slot['label'] }} <span class="text-error">*</span>
                </label>
                <span id="badge-{{ $slot['key'] }}"
                      class="hidden text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-medium">✓ Siap</span>
            </div>
            <p class="text-[12px] text-outline">{{ $slot['hint'] }}</p>
            <div id="preview-{{ $slot['key'] }}" class="hidden rounded-lg border border-slate-200 bg-slate-50 text-[12px] text-on-surface-variant p-sm flex items-center gap-2">
                <i id="preview-icon-{{ $slot['key'] }}" class="ti ti-file-description text-[18px]"></i>
                <span id="preview-name-{{ $slot['key'] }}" class="truncate flex-1"></span>
                <button type="button" onclick="clearSlot('{{ $slot['key'] }}')" class="text-error hover:text-error/70 flex-shrink-0">
                    <i class="ti ti-x text-[16px]"></i>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('file-{{ $slot['key'] }}').click()"
                        class="text-[12px] border border-outline-variant hover:border-secondary rounded-lg px-md py-xs text-on-surface-variant hover:text-secondary transition-colors">
                    Pilih file lain
                </button>
                <input type="file" id="file-{{ $slot['key'] }}" accept="{{ $slot['accept'] }}" class="hidden"
                       onchange="assignFile('{{ $slot['key'] }}', this.files[0])">
            </div>
            <input type="file" name="{{ $slot['key'] }}" id="real-{{ $slot['key'] }}" class="hidden" required>
            @error($slot['key']) <p class="text-[12px] text-error">{{ $message }}</p> @enderror
        </div>
        @endforeach
    </div>
</div>

{{-- Submit --}}
<div class="bg-white border border-slate-200 rounded-xl p-md shadow-sm flex justify-end">
    <button class="bg-primary hover:bg-primary-container text-white font-label-md py-2 px-xl rounded-lg transition-colors flex items-center gap-2">
        <i class="ti ti-send text-[18px]"></i>
        Kirim untuk Review
    </button>
</div>

</form>

@push('scripts')
<script>
function updateProgramInfo() {
    const val = document.querySelector('input[name="program_choice"]:checked')?.value;
    document.getElementById('info-pkppm').classList.toggle('hidden', val !== 'PKPPM');
    const hint = document.getElementById('loc-hint');
    if (val === 'KPM') hint.textContent = 'Sistem menggunakan koordinat ini untuk mencari desa KPM terdekat dari domisili Anda.';
    else if (val === 'PPL') hint.textContent = 'Sistem menggunakan koordinat ini untuk mencari sekolah PPL terdekat dari domisili Anda.';
    else hint.textContent = 'Untuk PKPPM, koordinat domisili digunakan sebagai referensi. Sistem akan memilih pasangan desa + sekolah yang paling berdekatan satu sama lain.';
}
document.querySelectorAll('input[name="program_choice"]').forEach(r => r.addEventListener('change', updateProgramInfo));
document.addEventListener('DOMContentLoaded', updateProgramInfo);

const hasOld = {{ old('latitude') ? 'true' : 'false' }};
const map = L.map('map').setView([{{ old('latitude', -6.2000) }}, {{ old('longitude', 106.8160) }}], hasOld ? 14 : 10);
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
    navigator.geolocation.getCurrentPosition(p => setCoords(p.coords.latitude, p.coords.longitude), e => alert('Gagal: ' + e.message));
}
const csrf = '{{ csrf_token() }}';
async function searchAddress() {
    const q = document.getElementById('address').value.trim();
    const out = document.getElementById('geo-results');
    if (q.length < 3) { out.innerHTML = '<p class="text-[12px] text-error">Masukkan alamat minimal 3 karakter.</p>'; return; }
    out.innerHTML = '<p class="text-[12px] text-on-surface-variant">Mencari…</p>';
    try {
        const r = await fetch(`{{ route('geocode') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin',
        });
        const data = await r.json();
        if (!data.results?.length) { out.innerHTML = '<p class="text-[12px] text-error">Lokasi tidak ditemukan.</p>'; return; }
        out.innerHTML = data.results.map(res =>
            `<button type="button" data-lat="${res.lat}" data-lng="${res.lon}"
                     class="block w-full text-left text-[12px] bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg px-md py-xs transition-colors">
                 ${res.display_name}</button>`).join('');
        out.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
            setCoords(b.dataset.lat, b.dataset.lng);
            out.innerHTML = '<p class="text-[12px] text-secondary font-medium">✓ Lokasi dipilih di peta</p>';
        }));
    } catch { out.innerHTML = '<p class="text-[12px] text-error">Gagal menghubungi layanan geocoding.</p>'; }
}

const slotOrder = ['transkrip','ktm','surat_pengantar','pas_foto'];
const slotFiles = { transkrip: null, ktm: null, surat_pengantar: null, pas_foto: null };
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.remove('border-secondary','bg-secondary/5');
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
    const iconEl = document.getElementById('preview-icon-'+key);
    iconEl.className = file.type.startsWith('image/') ? 'ti ti-photo text-[18px]' : 'ti ti-file-description text-[18px]';
    document.getElementById('preview-name-'+key).textContent = file.name;
    document.getElementById('preview-'+key).classList.remove('hidden');
    document.getElementById('badge-'+key).classList.remove('hidden');
    document.getElementById('slot-'+key).classList.add('border-secondary/40');
}
function clearSlot(key) {
    slotFiles[key] = null;
    document.getElementById('real-'+key).value = '';
    document.getElementById('preview-'+key).classList.add('hidden');
    document.getElementById('badge-'+key).classList.add('hidden');
    document.getElementById('slot-'+key).classList.remove('border-secondary/40');
}
slotOrder.forEach(key => {
    document.getElementById('file-'+key)?.addEventListener('change', function() {
        if (this.files[0]) assignFile(key, this.files[0]);
    });
});
document.addEventListener('DOMContentLoaded', () => map.invalidateSize());
</script>
@endpush
@endsection
