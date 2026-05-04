@extends('layouts.auth')
@section('title', 'Masuk')
@section('body-class', 'bg-overlay')
@section('footer-class', 'text-white/50 bg-transparent')

@section('body')
<main class="relative z-10 flex-grow flex items-center justify-center px-lg py-2xl">
    <div class="w-full max-w-[440px]">
        {{-- Branding --}}
        <div class="text-center mb-xl">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-xl shadow-lg mb-md">
                <i class="ti ti-school text-primary" style="font-size:40px"></i>
            </div>
            <h1 class="font-h1 text-h1 text-white tracking-tight mb-xs">SIPEP</h1>
            <p class="font-body-md text-surface-variant/80 tracking-wide">Portal Akademik Terpadu</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-surface-container-lowest rounded-xl shadow-2xl border border-outline-variant p-xl">
            <div class="mb-lg">
                <h2 class="font-h3 text-h3 text-on-surface">Selamat Datang</h2>
                <p class="font-body-sm text-on-surface-variant mt-xs">Silakan masuk dengan akun institusi Anda.</p>
            </div>

            {{-- Error --}}
            @error('email')
                <div class="mb-lg px-md py-sm rounded-lg bg-error-container border border-error/20 flex items-center gap-sm">
                    <i class="ti ti-alert-circle text-error text-[18px]"></i>
                    <p class="font-label-sm text-error">{{ $message }}</p>
                </div>
            @enderror

            <form method="POST" action="{{ route('login') }}" class="space-y-lg">
                @csrf
                {{-- Email --}}
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface block" for="email">Email Kampus</label>
                    <div class="relative group">
                        <i class="ti ti-mail absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-secondary transition-colors text-[20px]"></i>
                        <input class="w-full pl-12 pr-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent transition-all placeholder:text-outline font-body-sm text-on-surface"
                               id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               placeholder="nama@universitas.ac.id" required autocomplete="email"/>
                    </div>
                </div>

                {{-- Password --}}
                <div class="space-y-xs">
                    <div class="flex justify-between items-center">
                        <label class="font-label-md text-label-md text-on-surface" for="password">Password</label>
                    </div>
                    <div class="relative group">
                        <i class="ti ti-lock absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-secondary transition-colors text-[20px]"></i>
                        <input class="w-full pl-12 pr-12 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent transition-all placeholder:text-outline font-body-sm text-on-surface"
                               id="password" name="password" type="password"
                               placeholder="••••••••" required autocomplete="current-password"/>
                        <button class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors" type="button"
                                onclick="const i=document.getElementById('password');const pw=i.type==='password';i.type=pw?'text':'password';const ic=this.querySelector('i');ic.className=pw?'ti ti-eye-off text-[20px]':'ti ti-eye text-[20px]';">
                            <i class="ti ti-eye text-[20px]"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <label class="flex items-center gap-sm cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-outline-variant text-secondary focus:ring-secondary">
                    <span class="font-body-sm text-on-surface-variant">Ingat saya</span>
                </label>

                {{-- Submit --}}
                <div class="pt-md">
                    <button class="w-full bg-primary text-on-primary font-label-md py-4 rounded-lg shadow-md hover:bg-primary-container hover:shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2" type="submit">
                        <span>Masuk</span>
                        <i class="ti ti-login text-[20px]"></i>
                    </button>
                </div>
            </form>

            <div class="mt-xl pt-lg border-t border-outline-variant text-center">
                <p class="font-body-sm text-on-surface-variant">
                    Belum memiliki akun?
                    <a class="text-secondary font-label-md hover:underline ml-1" href="{{ route('register') }}">Daftar Akun Baru</a>
                </p>
            </div>
        </div>

        {{-- Security notice --}}
        <div class="mt-lg flex items-center justify-center gap-3 text-white/60">
            <i class="ti ti-shield-check text-[18px]"></i>
            <p class="font-label-sm">Koneksi aman terenkripsi SSL 256-bit</p>
        </div>
    </div>
</main>
@endsection
