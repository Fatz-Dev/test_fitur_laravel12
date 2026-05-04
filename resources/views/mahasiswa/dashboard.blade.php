@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Welcome Header --}}
<section class="flex flex-col md:flex-row md:items-end justify-between gap-md mb-lg">
    <div>
        <h2 class="font-h2 text-h2 text-primary">Selamat Datang!</h2>
        <p class="font-body-md text-body-md text-on-surface-variant">{{ auth()->user()->name }}</p>
    </div>
    @if(!$profile)
        <a href="{{ route('mahasiswa.profile.create') }}"
           class="px-md py-2 bg-secondary text-white rounded-lg font-label-md hover:opacity-90 transition-opacity flex items-center gap-2 text-sm">
            <i class="ti ti-file-pencil text-[18px]"></i>
            Lengkapi Profil
        </a>
    @endif
</section>

@if(!$profile)
    {{-- No profile yet --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-md mb-lg">
        <div class="flex items-start gap-md">
            <div class="bg-amber-100 p-3 rounded-lg flex-shrink-0">
                <i class="ti ti-alert-triangle text-amber-700 text-[28px]"></i>
            </div>
            <div>
                <p class="font-label-md text-amber-900 text-sm">Profil belum lengkap</p>
                <p class="font-body-sm text-amber-800 mt-1">Lengkapi data profil Anda agar dapat mengikuti program KPM &amp; PPL.</p>
            </div>
        </div>
        <a href="{{ route('mahasiswa.profile.create') }}"
           class="flex-shrink-0 bg-amber-600 hover:bg-amber-700 text-white font-label-md text-sm px-lg py-2 rounded-lg transition-colors">
            Lengkapi Sekarang
        </a>
    </div>

@else
    {{-- Status Cards --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-lg">
        {{-- Status Pendaftaran --}}
        <div class="bg-white p-lg rounded-xl shadow-[0_2px_4px_rgba(0,35,111,0.05)] border border-slate-200">
            @php
                $statusConfig = [
                    'pending'  => ['bg-amber-50 border-amber-200', 'text-amber-700',  'ti-clock-pause',    'Menunggu review admin'],
                    'approved' => ['bg-emerald-50 border-emerald-200', 'text-emerald-700', 'ti-circle-check', 'Profil telah disetujui'],
                    'rejected' => ['bg-red-50 border-red-200', 'text-error', 'ti-circle-x', 'Profil ditolak'],
                ];
                $cfg = $statusConfig[$profile->status] ?? $statusConfig['pending'];
            @endphp
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-2">Status Pendaftaran</p>
            <div class="flex items-center gap-2 mb-2">
                <i class="ti {{ $cfg[2] }} {{ $cfg[1] }} text-[22px]"></i>
                <span class="font-label-md {{ $cfg[1] }} capitalize">{{ $profile->status }}</span>
            </div>
            <p class="font-body-sm text-on-surface-variant text-[13px]">{{ $cfg[3] }}</p>
            @if($profile->admin_note)
                <div class="mt-3 p-2 bg-slate-50 rounded-lg border border-slate-200">
                    <p class="text-[12px] text-on-surface-variant">Catatan: {{ $profile->admin_note }}</p>
                </div>
            @endif
        </div>

        {{-- NIM & Microteaching --}}
        <div class="bg-white p-lg rounded-xl shadow-[0_2px_4px_rgba(0,35,111,0.05)] border border-slate-200">
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-2">Informasi Akademik</p>
            <p class="font-h3 text-h3 text-primary">{{ $profile->nim }}</p>
            <p class="font-body-sm text-on-surface-variant mt-1">NIM Mahasiswa</p>
            <div class="mt-3 flex items-center gap-2">
                <i class="ti ti-star text-secondary text-[18px]"></i>
                <span class="font-label-md text-on-surface">Microteaching: <span class="text-secondary">{{ $profile->microteaching_grade }}</span></span>
            </div>
        </div>

        {{-- Lokasi --}}
        <div class="bg-white p-lg rounded-xl shadow-[0_2px_4px_rgba(0,35,111,0.05)] border border-slate-200">
            <p class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-2">Lokasi Domisili</p>
            <p class="font-body-sm text-on-surface leading-relaxed">{{ $profile->address }}</p>
            <p class="text-[12px] text-outline mt-1">{{ $profile->latitude }}, {{ $profile->longitude }}</p>
            @if($profile->status === 'pending')
                <button onclick="document.getElementById('modal-lokasi').classList.remove('hidden')"
                        class="mt-3 flex items-center gap-1 text-sm text-secondary hover:text-primary font-label-md transition-colors">
                    <i class="ti ti-map-pin-edit text-[16px]"></i>
                    Perbarui Lokasi
                </button>
            @endif
        </div>
    </section>

    {{-- Gelombang Aktif --}}
    @if($profile->isApproved() || $profile->status === 'pending')
        <section class="grid grid-cols-1 md:grid-cols-2 gap-gutter mb-lg">
            @foreach(['KPM','PPL'] as $prog)
                <div class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2 mb-md">
                        <i class="ti ti-calendar text-[18px] {{ $prog === 'KPM' ? 'text-amber-600' : 'text-blue-600' }}"></i>
                        <p class="text-label-sm text-on-surface-variant uppercase tracking-wider">Gelombang Aktif {{ $prog }}</p>
                    </div>
                    @if($gelombang[$prog])
                        <p class="font-label-md text-on-surface">{{ $gelombang[$prog]->label() }}</p>
                        <p class="font-body-sm text-on-surface-variant mt-1">
                            {{ $gelombang[$prog]->tanggal_buka ? $gelombang[$prog]->tanggal_buka->format('d M Y') : '—' }}
                            &ndash;
                            {{ $gelombang[$prog]->tanggal_tutup ? $gelombang[$prog]->tanggal_tutup->format('d M Y') : '—' }}
                        </p>
                        @if($gelombang[$prog]->isOpen())
                            <span class="mt-2 inline-flex items-center gap-1 text-[12px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Terbuka
                            </span>
                        @else
                            <span class="mt-2 inline-flex items-center gap-1 text-[12px] bg-red-100 text-error px-2 py-0.5 rounded-full font-medium">
                                <span class="w-1.5 h-1.5 bg-error rounded-full"></span> Tutup
                            </span>
                        @endif
                    @else
                        <p class="font-body-sm text-outline italic">Belum ada gelombang aktif untuk {{ $prog }}.</p>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    {{-- Penempatan KPM & PPL --}}
    <section class="bg-white p-lg rounded-xl border border-slate-200 shadow-sm mb-lg">
        <div class="mb-lg">
            <h3 class="font-h3 text-h3 text-primary">Penempatan KPM &amp; PPL</h3>
            <p class="font-body-sm text-on-surface-variant mt-1">Penempatan ditetapkan otomatis berdasarkan domisili dan ketersediaan kuota lokasi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
            @foreach(['KPM','PPL'] as $prog)
                @php $reg = $registrations->firstWhere('program', $prog); @endphp
                <div class="border border-slate-200 rounded-xl p-lg {{ $prog === 'KPM' ? 'border-l-4 border-l-amber-400' : 'border-l-4 border-l-blue-400' }}">
                    <div class="flex items-center justify-between mb-md">
                        <span class="font-label-md text-[13px] px-2 py-0.5 rounded font-bold {{ $prog === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $prog }}
                        </span>
                        @if($reg && $reg->gelombang)
                            <span class="text-[12px] bg-primary/10 text-primary px-2 py-0.5 rounded font-medium">
                                {{ $reg->gelombang->label() }}
                            </span>
                        @endif
                    </div>

                    @if($reg)
                        <p class="font-label-md text-on-surface">{{ $reg->school->name }}</p>
                        <p class="font-body-sm text-on-surface-variant mt-1">
                            {{ $reg->school->locationType() }}
                            @if($reg->school->jenjang) &bull; {{ $reg->school->jenjang }}@endif
                        </p>
                        <p class="text-[12px] text-outline mt-1">{{ $reg->school->address }}</p>
                        <div class="flex items-center gap-1 mt-2">
                            <i class="ti ti-navigation text-[14px] text-outline"></i>
                            <p class="text-[12px] text-on-surface-variant">{{ number_format($reg->distance_km, 2) }} km dari domisili</p>
                        </div>
                        <div class="flex items-center justify-between mt-lg">
                            @php $badges = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-error/10 text-error','cancelled'=>'bg-slate-100 text-slate-600']; @endphp
                            <span class="text-[12px] px-3 py-1 rounded-full font-medium {{ $badges[$reg->status] ?? 'bg-slate-100 text-slate-600' }} capitalize">
                                {{ $reg->status }}
                            </span>
                            @if($reg->status !== 'approved')
                                <form method="POST" action="{{ route('mahasiswa.registrations.cancel', $reg) }}"
                                      onsubmit="return confirm('Batalkan penempatan {{ $prog }}?');">
                                    @csrf @method('DELETE')
                                    <button class="text-[12px] text-error hover:underline font-medium">Batalkan</button>
                                </form>
                            @endif
                        </div>
                    @elseif($profile->isApproved())
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <i class="ti ti-map-pin-off text-[36px] text-error/40"></i>
                            <p class="font-body-sm text-error mt-2">Tidak ada lokasi dengan kuota tersedia.</p>
                            <p class="text-[12px] text-on-surface-variant mt-1">Hubungi admin untuk bantuan.</p>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <i class="ti ti-hourglass text-[36px] text-outline/40"></i>
                            <p class="font-body-sm text-on-surface-variant mt-2">Menunggu persetujuan profil.</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif

{{-- Modal Update Lokasi --}}
<div id="modal-lokasi" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg border border-outline-variant">
        <div class="flex justify-between items-center px-xl py-lg border-b border-outline-variant">
            <h3 class="font-h3 text-on-surface" style="font-size:20px">Perbarui Lokasi Domisili</h3>
            <button onclick="document.getElementById('modal-lokasi').classList.add('hidden')"
                    class="text-outline hover:text-on-surface transition-colors p-1 rounded-lg hover:bg-slate-100">
                <i class="ti ti-x text-[20px]"></i>
            </button>
        </div>

        <div class="px-xl py-lg">
            <div class="flex items-start gap-sm mb-lg p-sm bg-surface-container-low rounded-lg border border-surface-variant">
                <i class="ti ti-info-circle text-primary text-[18px]"></i>
                <p class="font-label-sm text-on-primary-fixed-variant text-[12px]">Lokasi ini digunakan sistem untuk menetapkan lokasi KPM/PPL terdekat. Hanya dapat diubah saat profil masih menunggu review.</p>
            </div>

            <form method="POST" action="{{ route('mahasiswa.profile.location') }}" class="space-y-md">
                @csrf
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block">Alamat Tempat Tinggal</label>
                    <textarea name="address" id="modal-address" rows="2" required
                              class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">{{ $profile->address ?? '' }}</textarea>
                    <div class="flex gap-2 mt-2">
                        <button type="button" onclick="modalSearchAddress()"
                                class="flex items-center gap-1 text-[12px] bg-secondary/10 text-secondary hover:bg-secondary/20 px-md py-xs rounded-lg font-medium transition-colors">
                            <i class="ti ti-search text-[16px]"></i>
                            Cari via OpenStreetMap
                        </button>
                        <button type="button" onclick="modalUseGeo()"
                                class="flex items-center gap-1 text-[12px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-md py-xs rounded-lg font-medium transition-colors">
                            <i class="ti ti-current-location text-[16px]"></i>
                            Lokasi Saya
                        </button>
                    </div>
                    <div id="modal-geo-results" class="mt-2 space-y-1"></div>
                </div>

                <div class="grid grid-cols-2 gap-md">
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block">Latitude</label>
                        <input name="latitude" id="modal-lat" type="number" step="any"
                               value="{{ $profile->latitude ?? '' }}" required
                               class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary outline-none">
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block">Longitude</label>
                        <input name="longitude" id="modal-lng" type="number" step="any"
                               value="{{ $profile->longitude ?? '' }}" required
                               class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                            onclick="document.getElementById('modal-lokasi').classList.add('hidden')"
                            class="text-sm px-lg py-2 border border-outline-variant rounded-lg hover:bg-slate-50 font-label-md">Batal</button>
                    <button type="submit"
                            class="text-sm bg-primary hover:bg-primary-container text-white px-lg py-2 rounded-lg font-label-md transition-colors">
                        Simpan Lokasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
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
    if (q.length < 3) { out.innerHTML = '<p class="text-[12px] text-error">Masukkan alamat minimal 3 karakter.</p>'; return; }
    out.innerHTML = '<p class="text-[12px] text-on-surface-variant">Mencari...</p>';
    try {
        const r = await fetch(`{{ route('geocode') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin',
        });
        const data = await r.json();
        if (!data.results || data.results.length === 0) {
            out.innerHTML = '<p class="text-[12px] text-error">Lokasi tidak ditemukan.</p>'; return;
        }
        out.innerHTML = data.results.map(res => `
            <button type="button" data-lat="${res.lat}" data-lng="${res.lon}"
                    class="block w-full text-left text-[12px] bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg px-md py-sm transition-colors">
                ${res.display_name}
            </button>
        `).join('');
        out.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
            document.getElementById('modal-lat').value = parseFloat(b.dataset.lat).toFixed(7);
            document.getElementById('modal-lng').value = parseFloat(b.dataset.lng).toFixed(7);
            out.innerHTML = `<p class="text-[12px] text-secondary font-medium">✓ Koordinat dipilih: ${b.dataset.lat}, ${b.dataset.lng}</p>`;
        }));
    } catch (e) {
        out.innerHTML = '<p class="text-[12px] text-error">Gagal menghubungi layanan geocoding.</p>';
    }
}
</script>
@endpush
@endsection
