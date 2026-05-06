@extends('layouts.app')
@section('title', 'Ubah Password')

@section('content')
<div class="mb-lg">
    <h2 class="font-h2 text-h2 text-primary">Ubah Password</h2>
    <p class="font-body-sm text-on-surface-variant">Pastikan password baru Anda minimal 8 karakter.</p>
</div>

<div class="max-w-lg">
    <div class="bg-white border border-slate-200 rounded-xl p-xl shadow-sm">

        @if(session('status'))
            <div class="mb-lg px-md py-sm rounded-lg bg-secondary/10 border border-secondary/20 flex items-center gap-sm">
                <i class="ti ti-circle-check text-secondary text-[18px]"></i>
                <p class="font-label-sm text-secondary">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('account.password.update') }}" class="space-y-md">
            @csrf @method('PUT')

            {{-- Current Password --}}
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block" for="current_password">
                    Password Saat Ini
                </label>
                <div class="relative group">
                    <i class="ti ti-lock absolute left-md top-1/2 -translate-y-1/2 text-outline group-focus-within:text-secondary transition-colors text-[20px]"></i>
                    <input id="current_password" name="current_password" type="password" required
                           autocomplete="current-password"
                           class="w-full pl-xl pr-12 py-sm border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                           placeholder="••••••••"/>
                    <button type="button" tabindex="-1"
                            onclick="togglePass('current_password', this)"
                            class="absolute right-md top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors">
                        <i class="ti ti-eye text-[20px]"></i>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-[12px] text-error mt-1 flex items-center gap-1">
                        <i class="ti ti-alert-circle text-[14px]"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            <hr class="border-slate-100"/>

            {{-- New Password --}}
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block" for="password">
                    Password Baru
                </label>
                <div class="relative group">
                    <i class="ti ti-lock-open absolute left-md top-1/2 -translate-y-1/2 text-outline group-focus-within:text-secondary transition-colors text-[20px]"></i>
                    <input id="password" name="password" type="password" required
                           autocomplete="new-password"
                           class="w-full pl-xl pr-12 py-sm border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                           placeholder="Minimal 8 karakter"
                           oninput="checkStrength(this.value)"/>
                    <button type="button" tabindex="-1"
                            onclick="togglePass('password', this)"
                            class="absolute right-md top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors">
                        <i class="ti ti-eye text-[20px]"></i>
                    </button>
                </div>
                {{-- Strength bar --}}
                <div class="flex gap-1 mt-1" id="strength-bar">
                    <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="s1"></div>
                    <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="s2"></div>
                    <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="s3"></div>
                    <div class="h-1 flex-1 rounded-full bg-slate-200 transition-colors" id="s4"></div>
                </div>
                <p id="strength-label" class="text-[11px] text-outline"></p>
                @error('password')
                    <p class="text-[12px] text-error mt-1 flex items-center gap-1">
                        <i class="ti ti-alert-circle text-[14px]"></i>{{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Confirm New Password --}}
            <div class="space-y-xs">
                <label class="font-label-md text-label-md text-on-surface block" for="password_confirmation">
                    Konfirmasi Password Baru
                </label>
                <div class="relative group">
                    <i class="ti ti-shield-check absolute left-md top-1/2 -translate-y-1/2 text-outline group-focus-within:text-secondary transition-colors text-[20px]"></i>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           autocomplete="new-password"
                           class="w-full pl-xl pr-12 py-sm border border-outline-variant rounded-lg focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all"
                           placeholder="Ulangi password baru"/>
                    <button type="button" tabindex="-1"
                            onclick="togglePass('password_confirmation', this)"
                            class="absolute right-md top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors">
                        <i class="ti ti-eye text-[20px]"></i>
                    </button>
                </div>
            </div>

            <div class="flex justify-end pt-sm">
                <button type="submit"
                        class="bg-primary hover:bg-primary-container text-white font-label-md py-2 px-lg rounded-lg transition-colors flex items-center gap-2">
                    <i class="ti ti-device-floppy text-[18px]"></i>
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePass(id, btn) {
    const inp = document.getElementById(id);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.querySelector('i').className = show ? 'ti ti-eye-off text-[20px]' : 'ti ti-eye text-[20px]';
}
function checkStrength(v) {
    let score = 0;
    if (v.length >= 8)  score++;
    if (v.length >= 12) score++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
    if (/[0-9]/.test(v) && /[^A-Za-z0-9]/.test(v)) score++;
    const colors = ['', 'bg-red-400', 'bg-amber-400', 'bg-blue-400', 'bg-emerald-500'];
    const labels = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
    const textColors = ['', 'text-red-500', 'text-amber-500', 'text-blue-500', 'text-emerald-600'];
    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('s' + i);
        el.className = 'h-1 flex-1 rounded-full transition-colors ' + (i <= score ? colors[score] : 'bg-slate-200');
    }
    const lbl = document.getElementById('strength-label');
    lbl.textContent = v.length ? 'Kekuatan: ' + (labels[score] || 'Lemah') : '';
    lbl.className = 'text-[11px] ' + (textColors[score] || 'text-outline');
}
</script>
@endpush
@endsection
