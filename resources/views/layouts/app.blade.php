<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KPM-PPL Manager')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <style>body{font-family:'Inter',ui-sans-serif,system-ui;}</style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">
@auth
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('mahasiswa.dashboard') }}"
                   class="font-bold text-lg text-indigo-700">
                    KPM-PPL Manager
                </a>
                @if(auth()->user()->isAdmin())
                    <nav class="hidden md:flex gap-4 text-sm">
                        <a href="{{ route('admin.dashboard') }}"
                           class="hover:text-indigo-600 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-700 font-semibold' : '' }}">Dashboard</a>
                        <a href="{{ route('admin.mahasiswa.index') }}"
                           class="hover:text-indigo-600 {{ request()->routeIs('admin.mahasiswa.*') ? 'text-indigo-700 font-semibold' : '' }}">Mahasiswa</a>
                        <a href="{{ route('admin.schools.index') }}"
                           class="hover:text-indigo-600 {{ request()->routeIs('admin.schools.*') ? 'text-indigo-700 font-semibold' : '' }}">Lokasi</a>
                        <a href="{{ route('admin.gelombang.index') }}"
                           class="hover:text-indigo-600 {{ request()->routeIs('admin.gelombang.*') ? 'text-indigo-700 font-semibold' : '' }}">Gelombang</a>
                        <a href="{{ route('admin.registrations.index') }}"
                           class="hover:text-indigo-600 {{ request()->routeIs('admin.registrations.*') ? 'text-indigo-700 font-semibold' : '' }}">Penempatan</a>
                        <a href="{{ route('admin.settings.edit') }}"
                           class="hover:text-indigo-600 {{ request()->routeIs('admin.settings.*') ? 'text-indigo-700 font-semibold' : '' }}">Pengaturan</a>
                    </nav>
                @else
                    <nav class="hidden md:flex gap-4 text-sm">
                        <a href="{{ route('mahasiswa.dashboard') }}"
                           class="hover:text-indigo-600 {{ request()->routeIs('mahasiswa.dashboard') ? 'text-indigo-700 font-semibold' : '' }}">Dashboard</a>
                    </nav>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-600">{{ auth()->user()->name }}
                    <span class="ml-1 inline-block px-2 py-0.5 text-xs rounded {{ auth()->user()->isAdmin() ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="text-sm text-slate-600 hover:text-rose-600">Keluar</button>
                </form>
            </div>
        </div>
    </header>
@endauth

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 py-6">
        @if(session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
            <div class="mb-4 rounded-md bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </div>
</main>

<footer class="border-t border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 py-4 text-xs text-slate-500 text-center">
        &copy; {{ date('Y') }} KPM-PPL Manager
    </div>
</footer>
</body>
</html>
