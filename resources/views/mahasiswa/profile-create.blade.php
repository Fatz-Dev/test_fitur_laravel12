@extends('layouts.app')
@section('title', 'Lengkapi Pendaftaran')
@section('content')
<div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded p-6">
    <h1 class="text-xl font-bold mb-1">Lengkapi Pendaftaran</h1>
    <p class="text-sm text-slate-500 mb-6">Data ini akan direview admin. Setelah disetujui, sistem akan otomatis menetapkan penempatan KPM dan PPL berdasarkan lokasi domisili Anda.</p>

    <form method="POST" action="{{ route('mahasiswa.profile.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">NIM</label>
                <input name="nim" value="{{ old('nim') }}" required
                       class="w-full border border-slate-300 rounded px-3 py-2">
                @error('nim') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">No. HP (opsional)</label>
                <input name="phone" value="{{ old('phone') }}"
                       class="w-full border border-slate-300 rounded px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Alamat Tempat Tinggal</label>
            <textarea name="address" id="address" rows="2" required
                      class="w-full border border-slate-300 rounded px-3 py-2">{{ old('address') }}</textarea>
            @error('address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            <div class="flex gap-3 mt-2">
                <button type="button" onclick="searchAddress()"
                        class="text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-1 rounded">
                    Cari Koordinat dari Alamat (OpenStreetMap)
                </button>
                <button type="button" onclick="useGeo()"
                        class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-1 rounded">
                    Gunakan lokasi saya saat ini
                </button>
            </div>
            <div id="geo-results" class="mt-2 space-y-1"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Latitude</label>
                <input name="latitude" id="lat" type="number" step="any" value="{{ old('latitude') }}" required
                       class="w-full border border-slate-300 rounded px-3 py-2">
                @error('latitude') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Longitude</label>
                <input name="longitude" id="lng" type="number" step="any" value="{{ old('longitude') }}" required
                       class="w-full border border-slate-300 rounded px-3 py-2">
                @error('longitude') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Nilai Microteaching</label>
            <select name="microteaching_grade" required
                    class="w-full border border-slate-300 rounded px-3 py-2">
                @foreach(['A','B','C','D','E'] as $g)
                    <option value="{{ $g }}" @selected(old('microteaching_grade')===$g)>{{ $g }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Transkrip (PDF/Gambar)</label>
                <input name="transkrip" type="file" accept=".pdf,image/*" required class="w-full text-sm">
                @error('transkrip') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kartu Tanda Mahasiswa (KTM)</label>
                <input name="ktm" type="file" accept=".pdf,image/*" required class="w-full text-sm">
                @error('ktm') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Surat Pengantar</label>
                <input name="surat_pengantar" type="file" accept=".pdf,image/*" required class="w-full text-sm">
                @error('surat_pengantar') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Pas Foto (JPG/PNG)</label>
                <input name="pas_foto" type="file" accept="image/jpeg,image/png" required class="w-full text-sm">
                @error('pas_foto') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded">
                Kirim untuk Review
            </button>
        </div>
    </form>
</div>

<script>
const csrf = '{{ csrf_token() }}';

function useGeo() {
    if (!navigator.geolocation) return alert('Browser tidak mendukung geolokasi');
    navigator.geolocation.getCurrentPosition(p => {
        document.getElementById('lat').value = p.coords.latitude.toFixed(7);
        document.getElementById('lng').value = p.coords.longitude.toFixed(7);
    }, e => alert('Gagal mendapatkan lokasi: ' + e.message));
}

async function searchAddress() {
    const q = document.getElementById('address').value.trim();
    const out = document.getElementById('geo-results');
    if (q.length < 3) { out.innerHTML = '<p class="text-xs text-rose-600">Masukkan alamat minimal 3 karakter.</p>'; return; }
    out.innerHTML = '<p class="text-xs text-slate-500">Mencari...</p>';
    try {
        const r = await fetch(`{{ route('geocode') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin',
        });
        const data = await r.json();
        if (!data.results || data.results.length === 0) {
            out.innerHTML = '<p class="text-xs text-rose-600">Lokasi tidak ditemukan. Coba lebih spesifik.</p>';
            return;
        }
        out.innerHTML = data.results.map((res, i) => `
            <button type="button" data-lat="${res.lat}" data-lng="${res.lon}"
                    class="block w-full text-left text-xs bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded px-2 py-1">
                ${res.display_name}
            </button>
        `).join('');
        out.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
            document.getElementById('lat').value = parseFloat(b.dataset.lat).toFixed(7);
            document.getElementById('lng').value = parseFloat(b.dataset.lng).toFixed(7);
            out.innerHTML = `<p class="text-xs text-emerald-700">Koordinat dipilih: ${b.dataset.lat}, ${b.dataset.lng}</p>`;
        }));
    } catch (e) {
        out.innerHTML = '<p class="text-xs text-rose-600">Gagal menghubungi layanan geocoding.</p>';
    }
}
</script>
@endsection
