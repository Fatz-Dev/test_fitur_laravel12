@extends('layouts.app')
@section('title', 'Dashboard Mahasiswa')
@section('content')
<h1 class="text-2xl font-bold mb-4">Selamat datang, {{ auth()->user()->name }}</h1>

@if(! $profile)
    <div class="bg-amber-50 border border-amber-200 rounded p-4 flex items-center justify-between">
        <div>
            <p class="font-semibold text-amber-900">Profil belum lengkap</p>
            <p class="text-sm text-amber-800">Lengkapi pendaftaran agar dapat mengikuti KPM/PPL.</p>
        </div>
        <a href="{{ route('mahasiswa.profile.create') }}"
           class="bg-amber-600 hover:bg-amber-700 text-white text-sm px-4 py-2 rounded">Lengkapi Sekarang</a>
    </div>
@else
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-slate-200 rounded p-4">
            <p class="text-xs text-slate-500">Status Pendaftaran</p>
            @php $color = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose'][$profile->status]; @endphp
            <p class="text-lg font-semibold text-{{ $color }}-700 capitalize">{{ $profile->status }}</p>
            @if($profile->status === 'pending')
                <p class="text-xs text-slate-500 mt-1">Menunggu review admin. Setelah disetujui, penempatan KPM &amp; PPL akan ditetapkan otomatis oleh sistem.</p>
            @elseif($profile->status === 'approved')
                <p class="text-xs text-emerald-600 mt-1">Profil disetujui. Sistem telah menetapkan penempatan Anda.</p>
            @endif
            @if($profile->admin_note)
                <p class="text-xs text-slate-500 mt-2">Catatan admin: {{ $profile->admin_note }}</p>
            @endif
        </div>
        <div class="bg-white border border-slate-200 rounded p-4">
            <p class="text-xs text-slate-500">NIM</p>
            <p class="text-lg font-semibold">{{ $profile->nim }}</p>
            <p class="text-xs text-slate-500 mt-1">Nilai Microteaching: <span class="font-semibold">{{ $profile->microteaching_grade }}</span></p>
        </div>
        <div class="bg-white border border-slate-200 rounded p-4">
            <p class="text-xs text-slate-500">Lokasi Domisili</p>
            <p class="text-sm">{{ $profile->address }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $profile->latitude }}, {{ $profile->longitude }}</p>
            @if($profile->status === 'pending')
                <button onclick="document.getElementById('modal-lokasi').classList.remove('hidden')"
                        class="mt-2 text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-2 py-1 rounded">
                    &#9998; Perbarui Lokasi Domisili
                </button>
            @endif
        </div>
    </div>

    {{-- Gelombang aktif --}}
    @if($profile->isApproved() || $profile->status === 'pending')
    <div class="grid md:grid-cols-2 gap-3 mb-6">
        @foreach(['KPM','PPL'] as $prog)
            <div class="bg-white border border-slate-200 rounded p-4 text-sm">
                <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Gelombang Aktif {{ $prog }}</p>
                @if($gelombang[$prog])
                    <p class="font-semibold text-slate-700">{{ $gelombang[$prog]->label() }}</p>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $gelombang[$prog]->tanggal_buka ? $gelombang[$prog]->tanggal_buka->format('d M Y') : '—' }}
                        &ndash;
                        {{ $gelombang[$prog]->tanggal_tutup ? $gelombang[$prog]->tanggal_tutup->format('d M Y') : '—' }}
                    </p>
                    @if($gelombang[$prog]->isOpen())
                        <span class="mt-1 inline-block text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded">Terbuka</span>
                    @else
                        <span class="mt-1 inline-block text-xs bg-rose-100 text-rose-700 px-2 py-0.5 rounded">Tutup</span>
                    @endif
                @else
                    <p class="text-slate-400 italic text-xs">Belum ada gelombang aktif untuk {{ $prog }}.</p>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    {{-- Penempatan KPM & PPL --}}
    <div class="bg-white border border-slate-200 rounded p-4 mb-6">
        <h2 class="font-semibold mb-1">Penempatan KPM &amp; PPL</h2>
        <p class="text-xs text-slate-500 mb-4">Penempatan ditetapkan otomatis oleh sistem berdasarkan domisili dan ketersediaan kuota sekolah.</p>

        <div class="grid md:grid-cols-2 gap-3">
            @foreach(['KPM','PPL'] as $prog)
                @php $reg = $registrations->firstWhere('program', $prog); @endphp
                <div class="border border-slate-200 rounded p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-semibold text-slate-700">{{ $prog }}</p>
                        @if($reg && $reg->gelombang)
                            <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded">
                                {{ $reg->gelombang->label() }}
                            </span>
                        @endif
                    </div>
                    @if($reg)
                        <p class="text-sm font-medium text-slate-800">{{ $reg->school->name }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $reg->school->locationType() }}
                            @if($reg->school->jenjang) &bull; {{ $reg->school->jenjang }}@endif
                            &bull; {{ $reg->school->address }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">Jarak dari domisili: {{ number_format($reg->distance_km, 2) }} km</p>
                        <div class="flex items-center justify-between mt-3">
                            @php $sc = ['pending'=>'amber','approved'=>'emerald','rejected'=>'rose','cancelled'=>'slate'][$reg->status]; @endphp
                            <span class="text-xs px-2 py-1 rounded bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">{{ $reg->status }}</span>
                            @if($reg->status !== 'approved')
                                <form method="POST" action="{{ route('mahasiswa.registrations.cancel', $reg) }}"
                                      onsubmit="return confirm('Batalkan penempatan {{ $prog }}?');">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:underline">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    @elseif($profile->isApproved())
                        <p class="text-sm text-rose-600">Tidak ada sekolah dengan kuota tersedia. Hubungi admin.</p>
                    @else
                        <p class="text-sm text-slate-400 italic">Menunggu persetujuan profil oleh admin.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Modal Update Lokasi Domisili --}}
<div id="modal-lokasi" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg">Perbarui Lokasi Domisili</h3>
            <button onclick="document.getElementById('modal-lokasi').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Lokasi ini digunakan sistem untuk menetapkan sekolah KPM/PPL terdekat. Hanya dapat diubah saat profil masih menunggu review.</p>

        <form method="POST" action="{{ route('mahasiswa.profile.location') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Alamat Tempat Tinggal</label>
                <textarea name="address" id="modal-address" rows="2" required
                          class="w-full border border-slate-300 rounded px-3 py-2 text-sm">{{ $profile->address ?? '' }}</textarea>
                <div class="flex gap-2 mt-2">
                    <button type="button" onclick="modalSearchAddress()"
                            class="text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-1 rounded">
                        Cari Koordinat (OpenStreetMap)
                    </button>
                    <button type="button" onclick="modalUseGeo()"
                            class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-1 rounded">
                        Gunakan lokasi saya
                    </button>
                </div>
                <div id="modal-geo-results" class="mt-2 space-y-1"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Latitude</label>
                    <input name="latitude" id="modal-lat" type="number" step="any"
                           value="{{ $profile->latitude ?? '' }}" required
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Longitude</label>
                    <input name="longitude" id="modal-lng" type="number" step="any"
                           value="{{ $profile->longitude ?? '' }}" required
                           class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button"
                        onclick="document.getElementById('modal-lokasi').classList.add('hidden')"
                        class="text-sm px-4 py-2 border border-slate-300 rounded hover:bg-slate-50">Batal</button>
                <button class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    Simpan Lokasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const csrf = '{{ csrf_token() }}';
function modalUseGeo() {
    if (!navigator.geolocation) return alert('Browser tidak mendukung geolokasi');
    navigator.geolocation.getCurrentPosition(p => {
        document.getElementById('modal-lat').value = p.coords.latitude.toFixed(7);
        document.getElementById('modal-lng').value = p.coords.longitude.toFixed(7);
    }, e => alert('Gagal mendapatkan lokasi: ' + e.message));
}
async function modalSearchAddress() {
    const q = document.getElementById('modal-address').value.trim();
    const out = document.getElementById('modal-geo-results');
    if (q.length < 3) { out.innerHTML = '<p class="text-xs text-rose-600">Masukkan alamat minimal 3 karakter.</p>'; return; }
    out.innerHTML = '<p class="text-xs text-slate-500">Mencari...</p>';
    try {
        const r = await fetch(`{{ route('geocode') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin',
        });
        const data = await r.json();
        if (!data.results || data.results.length === 0) {
            out.innerHTML = '<p class="text-xs text-rose-600">Lokasi tidak ditemukan.</p>'; return;
        }
        out.innerHTML = data.results.map(res => `
            <button type="button" data-lat="${res.lat}" data-lng="${res.lon}"
                    class="block w-full text-left text-xs bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded px-2 py-1">
                ${res.display_name}
            </button>
        `).join('');
        out.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
            document.getElementById('modal-lat').value = parseFloat(b.dataset.lat).toFixed(7);
            document.getElementById('modal-lng').value = parseFloat(b.dataset.lng).toFixed(7);
            out.innerHTML = `<p class="text-xs text-emerald-700">Koordinat dipilih: ${b.dataset.lat}, ${b.dataset.lng}</p>`;
        }));
    } catch (e) {
        out.innerHTML = '<p class="text-xs text-rose-600">Gagal menghubungi layanan geocoding.</p>';
    }
}
</script>
@endsection
