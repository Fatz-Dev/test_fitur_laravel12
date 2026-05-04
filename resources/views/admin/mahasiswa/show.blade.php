@extends('layouts.app')
@section('title', 'Detail Mahasiswa')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- Back + Header --}}
<div class="mb-lg">
    <a href="{{ route('admin.mahasiswa.index') }}"
       class="inline-flex items-center gap-1 text-sm text-on-surface-variant hover:text-primary transition-colors mb-md font-label-md">
        <i class="ti ti-arrow-left text-[18px]"></i> Kembali
    </a>
    <div class="flex items-center gap-md">
        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
            {{ strtoupper(substr($mahasiswa->user->name, 0, 1)) }}
        </div>
        <div>
            <h2 class="font-h2 text-h2 text-primary">{{ $mahasiswa->user->name }}</h2>
            <p class="font-body-sm text-on-surface-variant">{{ $mahasiswa->user->email }}</p>
        </div>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-gutter mb-lg">
    {{-- Info Profil --}}
    <div class="bg-white border border-slate-200 rounded-xl p-lg md:col-span-2 shadow-sm">
        <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider text-[11px] mb-md">Informasi Profil</h3>
        <div class="grid md:grid-cols-2 gap-md text-sm">
            <div>
                <p class="text-on-surface-variant text-[12px] mb-1">NIM</p>
                <p class="font-label-md text-on-surface">{{ $mahasiswa->nim }}</p>
            </div>
            <div>
                <p class="text-on-surface-variant text-[12px] mb-1">No. HP</p>
                <p class="font-label-md text-on-surface">{{ $mahasiswa->phone ?? '-' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-on-surface-variant text-[12px] mb-1">Alamat</p>
                <p class="font-body-sm text-on-surface">{{ $mahasiswa->address }}</p>
                <p class="text-outline text-[11px] mt-1">{{ $mahasiswa->latitude }}, {{ $mahasiswa->longitude }}</p>
            </div>
            <div>
                <p class="text-on-surface-variant text-[12px] mb-1">Program</p>
                @php
                    $pc = $mahasiswa->program_choice ?? 'PKPPM';
                    $pcBadge = ['KPM'=>'bg-amber-100 text-amber-700','PPL'=>'bg-blue-100 text-blue-700','PKPPM'=>'bg-violet-100 text-violet-700'][$pc] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <span class="text-[12px] px-2 py-0.5 rounded-full {{ $pcBadge }} font-medium">{{ $mahasiswa->programChoiceLabel() }}</span>
            </div>
            <div>
                <p class="text-on-surface-variant text-[12px] mb-1">Nilai Microteaching</p>
                <span class="font-h3 text-secondary" style="font-size:20px">{{ $mahasiswa->microteaching_grade }}</span>
            </div>
        </div>

        <div class="mt-lg border-t border-slate-100 pt-md">
            <p class="text-on-surface-variant text-[12px] mb-md">Berkas Persyaratan</p>
            <div class="grid sm:grid-cols-2 gap-2">
                @foreach([
                    ['Transkrip', $mahasiswa->transkrip_path, 'ti-file-description'],
                    ['Kartu Tanda Mahasiswa', $mahasiswa->ktm_path, 'ti-id-badge'],
                    ['Surat Pengantar', $mahasiswa->surat_pengantar_path, 'ti-mail'],
                    ['Pas Foto', $mahasiswa->pas_foto_path, 'ti-user'],
                ] as [$label, $path, $icon])
                    @if($path)
                        <a target="_blank" href="{{ asset('storage/'.$path) }}"
                           class="flex items-center gap-2 p-sm rounded-lg border border-secondary/20 bg-secondary/5 hover:bg-secondary/10 transition-colors">
                            <i class="ti {{ $icon }} text-secondary text-[18px]"></i>
                            <span class="text-[12px] text-secondary font-medium">{{ $label }}</span>
                        </a>
                    @else
                        <div class="flex items-center gap-2 p-sm rounded-lg border border-slate-200 bg-slate-50 opacity-50">
                            <i class="ti {{ $icon }} text-outline text-[18px]"></i>
                            <span class="text-[12px] text-on-surface-variant">{{ $label }} — belum diunggah</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Panel Aksi --}}
    <div class="space-y-md">
        <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm">
            <p class="text-on-surface-variant text-[12px] mb-sm">Status Saat Ini</p>
            @php $badges = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-error/10 text-error']; @endphp
            <span class="text-sm px-3 py-1.5 rounded-full font-medium {{ $badges[$mahasiswa->status] ?? 'bg-slate-100 text-slate-600' }} capitalize">
                {{ $mahasiswa->status }}
            </span>
            @if($mahasiswa->admin_note)
                <div class="mt-sm p-sm bg-slate-50 rounded-lg border border-slate-200">
                    <p class="text-[12px] text-on-surface-variant">{{ $mahasiswa->admin_note }}</p>
                </div>
            @endif
        </div>

        @if($mahasiswa->status !== 'approved')
        <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm">
            <p class="font-label-md text-on-surface mb-sm text-sm">Setujui Mahasiswa</p>
            <form method="POST" action="{{ route('admin.mahasiswa.approve', $mahasiswa) }}" class="space-y-sm">
                @csrf
                <textarea name="admin_note" rows="2" placeholder="Catatan (opsional)"
                          class="w-full border border-outline-variant rounded-lg px-sm py-xs text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none resize-none"></textarea>
                <button class="w-full bg-secondary hover:opacity-90 text-white text-sm py-2 rounded-lg font-label-md flex items-center justify-center gap-2 transition-opacity">
                    <i class="ti ti-circle-check text-[18px]"></i>
                    Setujui &amp; Tetapkan Penempatan
                </button>
            </form>
        </div>
        @endif

        @if($mahasiswa->status !== 'rejected')
        <div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm">
            <p class="font-label-md text-on-surface mb-sm text-sm">Tolak Mahasiswa</p>
            <form method="POST" action="{{ route('admin.mahasiswa.reject', $mahasiswa) }}" class="space-y-sm">
                @csrf
                <textarea name="admin_note" rows="2" required placeholder="Alasan penolakan (wajib)"
                          class="w-full border border-outline-variant rounded-lg px-sm py-xs text-sm focus:ring-2 focus:ring-error/50 outline-none resize-none"></textarea>
                <button class="w-full bg-error hover:opacity-90 text-white text-sm py-2 rounded-lg font-label-md flex items-center justify-center gap-2 transition-opacity">
                    <i class="ti ti-circle-x text-[18px]"></i>
                    Tolak
                </button>
            </form>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.mahasiswa.destroy', $mahasiswa) }}"
              onsubmit="return confirm('Hapus mahasiswa beserta akunnya secara permanen?');">
            @csrf @method('DELETE')
            <button class="w-full text-[12px] text-error hover:underline font-medium py-2">
                Hapus mahasiswa
            </button>
        </form>
    </div>
</div>

{{-- Peta --}}
@if($mahasiswa->latitude && $mahasiswa->longitude)
<div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm mb-lg">
    <div class="flex items-center gap-md mb-md">
        <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider text-[11px]">Peta Lokasi</h3>
        <div class="flex flex-wrap gap-3 text-[12px] text-on-surface-variant">
            <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span> Domisili</span>
            @if($mahasiswa->registrations->where('program','KPM')->first())
                <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500"></span> KPM</span>
            @endif
            @if($mahasiswa->registrations->where('program','PPL')->first())
                <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-full bg-sky-500"></span> PPL</span>
            @endif
        </div>
    </div>
    <div id="map" class="rounded-lg border border-slate-200" style="height: 380px;"></div>
</div>
@endif

{{-- Riwayat Penempatan --}}
<div class="bg-white border border-slate-200 rounded-xl p-lg shadow-sm">
    <h3 class="font-label-md text-on-surface-variant uppercase tracking-wider text-[11px] mb-md">Riwayat Penempatan</h3>
    @if($mahasiswa->registrations->isEmpty())
        <div class="text-center py-8">
            <i class="ti ti-clipboard text-[40px] opacity-30 block mb-2"></i>
            <p class="font-body-sm text-on-surface-variant mt-2">Belum ada penempatan.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left border-b border-slate-100">
                    <tr>
                        <th class="pb-2 font-label-sm text-on-surface-variant uppercase tracking-wider text-[11px]">Program</th>
                        <th class="pb-2 font-label-sm text-on-surface-variant uppercase tracking-wider text-[11px]">Gelombang</th>
                        <th class="pb-2 font-label-sm text-on-surface-variant uppercase tracking-wider text-[11px]">Lokasi</th>
                        <th class="pb-2 font-label-sm text-on-surface-variant uppercase tracking-wider text-[11px]">Jarak</th>
                        <th class="pb-2 font-label-sm text-on-surface-variant uppercase tracking-wider text-[11px]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($mahasiswa->registrations as $r)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3">
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $r->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $r->program }}
                            </span>
                        </td>
                        <td class="py-3 text-[12px] text-on-surface-variant">{{ $r->gelombang ? $r->gelombang->label() : '-' }}</td>
                        <td class="py-3">
                            <p class="font-label-md text-on-surface">{{ $r->school->name }}</p>
                            <p class="text-[12px] text-on-surface-variant">{{ $r->school->locationType() }}</p>
                        </td>
                        <td class="py-3 text-[12px] text-on-surface-variant">{{ number_format($r->distance_km, 2) }} km</td>
                        <td class="py-3">
                            @php $badges = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-error/10 text-error','cancelled'=>'bg-slate-100 text-slate-600']; @endphp
                            <span class="text-[12px] px-2 py-1 rounded-full font-medium {{ $badges[$r->status] ?? 'bg-slate-100 text-slate-600' }} capitalize">
                                {{ $r->status }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($mahasiswa->latitude && $mahasiswa->longitude)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const homeLat = {{ $mahasiswa->latitude }};
    const homeLng = {{ $mahasiswa->longitude }};
    const map = L.map('map').setView([homeLat, homeLng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);
    function colorIcon(color) {
        const colors = { blue: '#3B82F6', amber: '#F59E0B', sky: '#0EA5E9' };
        const c = colors[color] || '#6366F1';
        return L.divIcon({
            className: '',
            html: `<div style="width:14px;height:14px;border-radius:50%;background:${c};border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>`,
            iconSize: [14, 14], iconAnchor: [7, 7]
        });
    }
    const bounds = [];
    L.marker([homeLat, homeLng], { icon: colorIcon('blue') })
        .addTo(map)
        .bindPopup(`<b>Domisili</b><br>${{!! json_encode($mahasiswa->address) !!}}`);
    bounds.push([homeLat, homeLng]);
    @foreach($mahasiswa->registrations->load('school') as $r)
    @if($r->school)
    (function() {
        const prog  = '{{ $r->program }}';
        const sLat  = {{ $r->school->latitude }};
        const sLng  = {{ $r->school->longitude }};
        const sName = {!! json_encode($r->school->name) !!};
        const sType = {!! json_encode($r->school->locationType()) !!};
        const dist  = '{{ number_format($r->distance_km, 2) }} km';
        const color = prog === 'KPM' ? 'amber' : 'sky';
        L.marker([sLat, sLng], { icon: colorIcon(color) })
            .addTo(map)
            .bindPopup(`<b>${prog} — ${sType}</b><br>${sName}<br><small>${dist}</small>`);
        L.polyline([[homeLat, homeLng], [sLat, sLng]], {
            color: prog === 'KPM' ? '#F59E0B' : '#0EA5E9', weight: 2, dashArray: '5,5', opacity: 0.7
        }).addTo(map);
        bounds.push([sLat, sLng]);
    })();
    @endif
    @endforeach
    if (bounds.length > 1) map.fitBounds(bounds, { padding: [40, 40] });
});
</script>
@endpush
@endif
@endsection
