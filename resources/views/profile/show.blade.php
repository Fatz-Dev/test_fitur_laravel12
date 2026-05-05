@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')

<div class="max-w-3xl mx-auto">

    <div class="flex items-center justify-between mb-lg">
        <div>
            <h2 class="font-h2 text-h2 text-primary">Profil Saya</h2>
            <p class="font-body-sm text-on-surface-variant">Kelola informasi akun Anda.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-md bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-lg py-md flex items-center gap-2">
            <i class="ti ti-circle-check text-[18px] text-emerald-600"></i>
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-md bg-error/10 border border-error/30 text-error rounded-xl px-lg py-md">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Account Info --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-lg">
        <div class="px-lg py-md border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
            <i class="ti ti-user text-primary text-[20px]"></i>
            <h3 class="font-h3 text-h3 text-on-surface">Informasi Akun</h3>
        </div>

        {{-- Avatar --}}
        <div class="px-lg pt-lg pb-md flex items-center gap-md border-b border-slate-100">
            <div class="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-3xl flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h4 class="font-h3 text-h3 text-on-surface">{{ $user->name }}</h4>
                <p class="text-on-surface-variant font-body-sm">{{ $user->email }}</p>
                <span class="mt-1 inline-block px-3 py-0.5 text-[11px] font-bold rounded-full
                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : ($user->role === 'supervisor' ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700') }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="p-lg space-y-md">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block">Nama Lengkap <span class="text-error">*</span></label>
                    <input name="name" type="text" required
                           value="{{ old('name', $user->name) }}"
                           class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                </div>
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block">Email <span class="text-error">*</span></label>
                    <input name="email" type="email" required
                           value="{{ old('email', $user->email) }}"
                           class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                </div>
            </div>

            <div class="border-t border-slate-100 pt-md">
                <p class="font-label-md text-on-surface mb-md">Ubah Password <span class="text-[12px] font-normal text-on-surface-variant">(kosongkan jika tidak ingin diubah)</span></p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block">Password Saat Ini</label>
                        <input name="current_password" type="password"
                               placeholder="••••••••"
                               class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block">Password Baru</label>
                        <input name="password" type="password"
                               placeholder="Min. 8 karakter"
                               class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block">Konfirmasi Password</label>
                        <input name="password_confirmation" type="password"
                               placeholder="Ulangi password baru"
                               class="w-full border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-sm">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary text-white px-lg py-2.5 rounded-lg font-label-md hover:bg-primary-container transition-colors">
                    <i class="ti ti-device-floppy text-[16px]"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Role-specific info --}}
    @if($user->isMahasiswa() && $extra)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-lg">
            <div class="px-lg py-md border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                <i class="ti ti-id-badge text-secondary text-[20px]"></i>
                <h3 class="font-h3 text-h3 text-on-surface">Data Akademik</h3>
            </div>
            <div class="p-lg space-y-md">
                <div class="grid grid-cols-2 gap-md">
                    <div>
                        <p class="text-[12px] text-on-surface-variant font-medium uppercase tracking-wider mb-1">NIM</p>
                        <p class="font-label-md text-on-surface">{{ $extra->nim }}</p>
                    </div>
                    <div>
                        <p class="text-[12px] text-on-surface-variant font-medium uppercase tracking-wider mb-1">Jurusan</p>
                        <p class="font-label-md text-on-surface">{{ $extra->jurusan ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[12px] text-on-surface-variant font-medium uppercase tracking-wider mb-1">No. HP</p>
                        <p class="font-label-md text-on-surface">{{ $extra->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[12px] text-on-surface-variant font-medium uppercase tracking-wider mb-1">Status</p>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold
                            {{ $extra->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($extra->status === 'rejected' ? 'bg-error/10 text-error' : 'bg-amber-100 text-amber-700') }}">
                            {{ ucfirst($extra->status ?? 'pending') }}
                        </span>
                    </div>
                </div>

                @if($extra->registrations && $extra->registrations->isNotEmpty())
                    <div class="border-t border-slate-100 pt-md">
                        <p class="text-[12px] text-on-surface-variant font-medium uppercase tracking-wider mb-md">Registrasi Program</p>
                        <div class="space-y-sm">
                            @foreach($extra->registrations as $reg)
                                <div class="flex items-center justify-between bg-surface-container-low border border-surface-variant rounded-lg px-md py-sm">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                                            {{ $reg->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $reg->program }}
                                        </span>
                                        <span class="text-[13px] text-on-surface">{{ $reg->school->name ?? '—' }}</span>
                                    </div>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                                        {{ $reg->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($reg->status === 'rejected' ? 'bg-error/10 text-error' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($reg->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

    @elseif($user->isSupervisor() && $extra && $extra->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-lg">
            <div class="px-lg py-md border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
                <i class="ti ti-school text-secondary text-[20px]"></i>
                <h3 class="font-h3 text-h3 text-on-surface">Sekolah / Lokasi Binaan</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($extra as $school)
                    <div class="px-lg py-md flex items-center justify-between">
                        <div class="flex items-center gap-md">
                            <div class="h-10 w-10 rounded-lg {{ $school->program === 'KPM' ? 'bg-amber-50' : 'bg-blue-50' }} flex items-center justify-center">
                                <i class="ti {{ $school->program === 'KPM' ? 'ti-home' : 'ti-school' }} text-[20px] {{ $school->program === 'KPM' ? 'text-amber-600' : 'text-blue-600' }}"></i>
                            </div>
                            <div>
                                <p class="font-label-md text-on-surface">{{ $school->name }}</p>
                                <p class="text-[12px] text-on-surface-variant flex items-center gap-1">
                                    <i class="ti ti-map-pin text-[12px]"></i>
                                    {{ Str::limit($school->address, 60) }}
                                </p>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                            {{ $school->program === 'KPM' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $school->program }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
