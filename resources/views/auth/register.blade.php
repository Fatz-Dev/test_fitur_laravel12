@extends('layouts.app')
@section('title', 'Daftar')
@section('content')
<div class="max-w-md mx-auto mt-8 bg-white rounded-lg shadow border border-slate-200 p-6">
    <h1 class="text-xl font-bold mb-1">Buat Akun Mahasiswa</h1>
    <p class="text-sm text-slate-500 mb-4">Lengkapi data dasar untuk membuat akun</p>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
            <input name="name" type="text" value="{{ old('name') }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email Kampus</label>
            <input name="email" type="email" value="{{ old('email') }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
            @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input name="password" type="password" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
            @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
            <input name="password_confirmation" type="password" required
                   class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded">Daftar</button>
    </form>
    <p class="text-sm text-center mt-4">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Masuk</a>
    </p>
</div>
@endsection
