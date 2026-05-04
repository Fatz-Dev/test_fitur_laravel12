@extends('layouts.auth')
@section('title', 'Daftar Akun')
@section('body-class', 'bg-institutional')
@section('footer-class', 'bg-slate-50 border-t border-slate-200 text-slate-500')

@section('body')
<main class="flex-grow flex items-center justify-center px-lg py-2xl">
    <div class="w-full max-w-[1100px] grid md:grid-cols-2 bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden border border-slate-200">

        {{-- Left panel: illustration --}}
        <div class="relative hidden md:block overflow-hidden">
            <img class="absolute inset-0 w-full h-full object-cover"
                 src="https://images.unsplash.com/photo-1562774053-701939374585?w=800&q=80"
                 alt="Kampus universitas"/>
            <div class="absolute inset-0 bg-primary/50 backdrop-blur-[2px] flex flex-col justify-end p-xl text-white">
                <h1 class="font-h1 text-h1 mb-md">SIPEP</h1>
                <p class="font-body-lg text-body-lg opacity-90">Sistem Informasi Praktik &amp; Edukasi Profesional. Gerbang menuju pengalaman akademik yang lebih terstruktur dan efisien.</p>
            </div>
        </div>

        {{-- Right panel: form --}}
        <div class="p-xl md:p-2xl flex flex-col justify-center">
            <div class="mb-xl">
                <h2 class="font-h2 text-h2 text-primary mb-xs">Daftar Akun</h2>
                <p class="font-body-md text-body-md text-outline">Silakan lengkapi data mahasiswa Anda di bawah ini.</p>
            </div>

            {{-- Errors --}}
            @if($errors->any())
                <div class="mb-lg px-md py-sm rounded-lg bg-error-container border border-error/20">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $err)
                            <li class="font-label-sm text-error flex items-center gap-xs">
                                <span class="material-symbols-outlined text-[16px]">error</span>{{ $err }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-md">
                @csrf
                {{-- Nama --}}
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block" for="name">Nama Lengkap</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline text-[20px]">person</span>
                        <input class="w-full pl-xl pr-md py-md bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all font-body-md text-on-surface"
                               id="name" name="name" type="text"
                               value="{{ old('name') }}"
                               placeholder="Masukkan nama sesuai KTM" required/>
                    </div>
                </div>

                {{-- Email --}}
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block" for="email">Email Kampus</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline text-[20px]">mail</span>
                        <input class="w-full pl-xl pr-md py-md bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all font-body-md text-on-surface"
                               id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               placeholder="nama@univ.ac.id" required autocomplete="email"/>
                    </div>
                    <div class="flex items-start gap-sm p-sm bg-surface-container-low rounded-lg border border-surface-variant">
                        <span class="material-symbols-outlined text-primary text-[18px] mt-0.5">info</span>
                        <p class="font-label-sm text-on-primary-fixed-variant">Gunakan email kampus resmi untuk verifikasi status mahasiswa aktif.</p>
                    </div>
                </div>

                {{-- Password grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block" for="password">Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline text-[20px]">lock</span>
                            <input class="w-full pl-xl pr-md py-md bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all font-body-md text-on-surface"
                                   id="password" name="password" type="password"
                                   placeholder="••••••••" required autocomplete="new-password"/>
                        </div>
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface block" for="password_confirmation">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-outline text-[20px]">enhanced_encryption</span>
                            <input class="w-full pl-xl pr-md py-md bg-surface-bright border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all font-body-md text-on-surface"
                                   id="password_confirmation" name="password_confirmation" type="password"
                                   placeholder="••••••••" required autocomplete="new-password"/>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-md">
                    <button class="w-full py-md bg-primary text-on-primary font-label-md text-body-md rounded-lg hover:bg-primary-container transition-all shadow-md active:scale-[0.98] duration-150 flex items-center justify-center gap-sm" type="submit">
                        Daftar
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </div>
            </form>

            <div class="mt-xl text-center border-t border-slate-100 pt-xl">
                <p class="font-body-md text-body-md text-outline">
                    Sudah punya akun?
                    <a class="text-secondary font-label-md hover:underline decoration-2 underline-offset-4" href="{{ route('login') }}">Masuk</a>
                </p>
            </div>
        </div>
    </div>
</main>
@endsection
