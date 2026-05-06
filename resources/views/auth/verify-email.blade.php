@extends('layouts.auth')
@section('title', 'Verifikasi Email')
@section('body-class', 'bg-overlay')
@section('footer-class', 'text-white/50 bg-transparent')

@section('body')
<main class="relative z-10 flex-grow flex items-center justify-center px-lg py-2xl">
    <div class="w-full max-w-[480px]">

        {{-- Icon --}}
        <div class="text-center mb-xl">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-lg mb-md">
                <i class="ti ti-mail-check text-secondary" style="font-size:48px"></i>
            </div>
            <h1 class="font-h2 text-h2 text-white mb-xs">Cek Email Anda</h1>
            <p class="text-white/70 text-sm">Kami telah mengirimkan link verifikasi</p>
        </div>

        <div class="bg-white rounded-xl shadow-2xl border border-outline-variant p-xl">

            {{-- Status / Error --}}
            @if(session('status'))
                <div class="mb-lg px-md py-sm rounded-lg bg-secondary/10 border border-secondary/20 flex items-start gap-sm">
                    <i class="ti ti-circle-check text-secondary text-[18px] mt-0.5"></i>
                    <p class="font-label-sm text-secondary">{{ session('status') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-lg px-md py-sm rounded-lg bg-error/10 border border-error/20 flex items-start gap-sm">
                    <i class="ti ti-alert-circle text-error text-[18px] mt-0.5"></i>
                    <p class="font-label-sm text-error">{{ session('error') }}</p>
                </div>
            @endif

            <div class="text-center mb-xl">
                <p class="font-body-sm text-on-surface-variant leading-relaxed">
                    Link verifikasi telah dikirim ke
                    @if(session('registered_email'))
                        <strong class="text-on-surface">{{ session('registered_email') }}</strong>.
                    @else
                        alamat email Anda.
                    @endif
                    <br/>Klik link tersebut untuk mengaktifkan akun.
                </p>
                <div class="mt-md p-sm bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-[12px] text-amber-800 flex items-center justify-center gap-1">
                        <i class="ti ti-clock text-[14px]"></i>
                        Link berlaku selama <strong>24 jam</strong>. Cek folder Spam jika tidak ada di Inbox.
                    </p>
                </div>
            </div>

            {{-- Resend Form --}}
            <div class="border-t border-slate-100 pt-lg">
                <p class="text-[13px] text-on-surface-variant text-center mb-md">Belum menerima email? Minta link baru:</p>
                <form method="POST" action="{{ route('email.resend') }}">
                    @csrf
                    <div class="flex gap-2">
                        <input name="email"
                               type="email"
                               value="{{ session('registered_email') ?? old('email') }}"
                               placeholder="Masukkan email Anda"
                               required
                               class="flex-1 border border-outline-variant rounded-lg px-md py-sm text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none"/>
                        <button type="submit"
                                class="bg-primary text-white px-md py-sm rounded-lg font-label-md text-sm hover:bg-primary-container transition-colors flex-shrink-0 flex items-center gap-1">
                            <i class="ti ti-send text-[16px]"></i>
                            Kirim Ulang
                        </button>
                    </div>
                    @error('email')
                        <p class="text-[12px] text-error mt-1">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>

        <div class="mt-lg text-center">
            <a href="{{ route('login') }}" class="text-white/70 hover:text-white text-sm flex items-center justify-center gap-1 transition-colors">
                <i class="ti ti-arrow-left text-[16px]"></i>
                Kembali ke halaman masuk
            </a>
        </div>
    </div>
</main>
@endsection
