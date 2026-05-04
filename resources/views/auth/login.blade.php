@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="max-w-md mx-auto mt-8 bg-white rounded-lg shadow border border-slate-200 p-6">
    <h1 class="text-xl font-bold mb-1">Masuk Akun</h1>
    <p class="text-sm text-slate-500 mb-4">KPM-PPL Manager</p>
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input name="password" type="password" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember"> Ingat saya
        </label>
        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded">Masuk</button>
    </form>
    <p class="text-sm text-center mt-4">
        Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Daftar</a>
    </p>
</div>
@endsection
