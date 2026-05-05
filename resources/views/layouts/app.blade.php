<!DOCTYPE html>
<html class="light" lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>@yield('title', 'SIPEP') - Portal Akademik</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('tabler-icons/tabler-icons.min.css') }}"/>
<script>
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'primary':                    '#00236f',
                'primary-container':          '#1e3a8a',
                'on-primary':                 '#ffffff',
                'on-primary-container':       '#90a8ff',
                'primary-fixed':              '#dce1ff',
                'primary-fixed-dim':          '#b6c4ff',
                'on-primary-fixed':           '#00164e',
                'on-primary-fixed-variant':   '#264191',
                'inverse-primary':            '#b6c4ff',
                'secondary':                  '#006a61',
                'secondary-container':        '#86f2e4',
                'on-secondary':               '#ffffff',
                'on-secondary-container':     '#006f66',
                'error':                      '#ba1a1a',
                'error-container':            '#ffdad6',
                'on-error':                   '#ffffff',
                'on-error-container':         '#93000a',
                'background':                 '#f8f9ff',
                'on-background':              '#0d1c2e',
                'surface':                    '#f8f9ff',
                'surface-bright':             '#f8f9ff',
                'surface-variant':            '#d5e3fc',
                'surface-container-lowest':   '#ffffff',
                'surface-container-low':      '#eff4ff',
                'surface-container':          '#e6eeff',
                'surface-container-high':     '#dce9ff',
                'surface-container-highest':  '#d5e3fc',
                'on-surface':                 '#0d1c2e',
                'on-surface-variant':         '#444651',
                'outline':                    '#757682',
                'outline-variant':            '#c5c5d3',
                'inverse-surface':            '#233144',
                'inverse-on-surface':         '#eaf1ff',
            },
            borderRadius: { DEFAULT: '0.25rem', lg: '0.5rem', xl: '0.75rem', '2xl': '1rem', full: '9999px' },
            spacing: { xs: '4px', sm: '8px', md: '16px', lg: '24px', xl: '32px', '2xl': '48px', gutter: '24px', base: '4px', 'container-max': '1280px' },
            fontFamily: {
                'h1': ['"Public Sans"'], 'h2': ['"Public Sans"'], 'h3': ['"Public Sans"'],
                'body-lg': ['"Public Sans"'], 'body-md': ['"Public Sans"'], 'body-sm': ['"Public Sans"'],
                'label-md': ['"Public Sans"'], 'label-sm': ['"Public Sans"'],
            },
            fontSize: {
                'h1':       ['36px', { lineHeight: '1.2', fontWeight: '700' }],
                'h2':       ['30px', { lineHeight: '1.3', fontWeight: '600' }],
                'h3':       ['24px', { lineHeight: '1.3', fontWeight: '600' }],
                'body-lg':  ['18px', { lineHeight: '1.6', fontWeight: '400' }],
                'body-md':  ['16px', { lineHeight: '1.5', fontWeight: '400' }],
                'body-sm':  ['14px', { lineHeight: '1.5', fontWeight: '400' }],
                'label-md': ['14px', { lineHeight: '1',   fontWeight: '600' }],
                'label-sm': ['12px', { lineHeight: '1',   fontWeight: '500' }],
            },
        },
    },
}
</script>
<style>
    body { font-family: 'Public Sans', sans-serif; }
    .ti { vertical-align: middle; line-height: 1; }
</style>
@stack('styles')
</head>
<body class="bg-background text-on-surface min-h-screen">

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

{{-- Sidebar --}}
<aside id="sidebar" class="fixed left-0 top-0 h-screen w-64 bg-blue-900 z-40 flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300">
    <div class="px-6 py-8">
        <h1 class="text-2xl font-black text-white tracking-tight">SIPEP</h1>
        <p class="text-teal-400 font-medium text-sm">Portal Akademik</p>
    </div>

    <nav class="flex-1 px-2 space-y-1 overflow-y-auto">

        {{-- ── ADMIN ── --}}
        @if(auth()->user()?->isAdmin())
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-layout-dashboard text-[20px]"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.mahasiswa.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.mahasiswa.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-user-check text-[20px]"></i>
                <span>Mahasiswa</span>
            </a>
            <a href="{{ route('admin.schools.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.schools.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-map-pin text-[20px]"></i>
                <span>Lokasi</span>
            </a>
            <a href="{{ route('admin.gelombang.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.gelombang.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-calendar text-[20px]"></i>
                <span>Gelombang</span>
            </a>
            <a href="{{ route('admin.registrations.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.registrations.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-clipboard-check text-[20px]"></i>
                <span>Penempatan</span>
            </a>

            {{-- SIPEP Class divider --}}
            <div class="px-4 pt-4 pb-1">
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">SIPEP Class</p>
            </div>
            <a href="{{ route('admin.class.assignments.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.class.assignments.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-clipboard-list text-[20px]"></i>
                <span>Tugas</span>
            </a>
            <a href="{{ route('admin.class.grades') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.class.grades') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-award text-[20px]"></i>
                <span>Nilai</span>
            </a>
            <a href="{{ route('admin.supervisors.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.supervisors.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-users-group text-[20px]"></i>
                <span>Supervisor</span>
            </a>

            {{-- Settings --}}
            <div class="px-4 pt-4 pb-1">
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">Sistem</p>
            </div>
            <a href="{{ route('admin.settings.edit') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('admin.settings.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-settings text-[20px]"></i>
                <span>Pengaturan</span>
            </a>
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('profile.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-user-circle text-[20px]"></i>
                <span>Profil Saya</span>
            </a>

        {{-- ── SUPERVISOR ── --}}
        @elseif(auth()->user()?->isSupervisor())
            <a href="{{ route('supervisor.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('supervisor.dashboard') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-layout-dashboard text-[20px]"></i>
                <span>Dashboard</span>
            </a>
            <div class="px-4 pt-4 pb-1">
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">SIPEP Class</p>
            </div>
            <a href="{{ route('supervisor.classes.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('supervisor.classes.index') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-chalkboard text-[20px]"></i>
                <span>Daftar Kelas</span>
            </a>
            @foreach(auth()->user()->supervisorSchools as $sSchool)
                <a href="{{ route('supervisor.classes.show', $sSchool) }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                          {{ request()->routeIs('supervisor.classes.show') && request()->route('school')?->id == $sSchool->id ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                    <i class="ti {{ $sSchool->program === 'KPM' ? 'ti-home' : 'ti-school' }} text-[20px]"></i>
                    <span class="truncate">{{ $sSchool->name }}</span>
                </a>
            @endforeach
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('profile.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-user-circle text-[20px]"></i>
                <span>Profil Saya</span>
            </a>

        {{-- ── MAHASISWA ── --}}
        @else
            <a href="{{ route('mahasiswa.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-layout-dashboard text-[20px]"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('mahasiswa.profile.create') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('mahasiswa.profile.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-id-badge text-[20px]"></i>
                <span>Data Akademik</span>
            </a>
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('profile.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-user-circle text-[20px]"></i>
                <span>Profil Saya</span>
            </a>
            <div class="px-4 pt-4 pb-1">
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">SIPEP Class</p>
            </div>
            <a href="{{ route('mahasiswa.class.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                      {{ request()->routeIs('mahasiswa.class.*') ? 'bg-blue-800 text-teal-400 border-l-4 border-teal-400' : 'text-slate-300 hover:text-white hover:bg-blue-800/50' }}">
                <i class="ti ti-school text-[20px]"></i>
                <span>Kelas Saya</span>
            </a>
        @endif
    </nav>

    <div class="p-4 border-t border-blue-800">
        <div class="flex items-center gap-3 px-4 py-2 mb-2">
            <div class="h-8 w-8 rounded-full bg-teal-400 flex items-center justify-center text-blue-900 font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-white text-sm font-medium truncate">{{ auth()->user()?->name }}</p>
                <p class="text-teal-400 text-xs capitalize">{{ auth()->user()?->role }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-3 px-4 py-2 text-slate-300 hover:text-white w-full hover:bg-blue-800/50 rounded-lg transition-all text-sm">
                <i class="ti ti-logout text-[20px]"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

{{-- Main canvas --}}
<div class="md:ml-64 min-h-screen flex flex-col">

    {{-- Top AppBar --}}
    <header class="sticky top-0 z-20 flex items-center justify-between px-4 md:px-6 h-16 bg-white border-b border-slate-200 shadow-sm">
        <div class="flex items-center gap-4 flex-1">
            <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                <i class="ti ti-menu-2 text-[22px]"></i>
            </button>
            <span class="md:hidden font-bold text-primary text-sm">SIPEP</span>
            <div class="relative hidden md:block w-full max-w-md">
                <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]"></i>
                <input class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/30"
                       placeholder="Cari data mahasiswa atau lokasi..." type="text"/>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button class="p-2 text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                <i class="ti ti-bell text-[20px]"></i>
            </button>
            <div class="h-8 w-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
        </div>
    </header>

    @if(session('status'))
        <div class="mx-6 mt-4 px-4 py-3 rounded-xl bg-secondary/10 border border-secondary/30 text-secondary text-sm flex items-center gap-2">
            <i class="ti ti-circle-check text-[18px]"></i>
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mx-6 mt-4 px-4 py-3 rounded-xl bg-error/10 border border-error/30 text-error text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <main class="flex-1 p-4 md:p-6 lg:p-8 max-w-[1280px] w-full mx-auto">
        @yield('content')
    </main>

    <footer class="mt-auto w-full py-4 px-6 flex flex-col md:flex-row justify-between items-center gap-2 bg-slate-50 border-t border-slate-200 text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <span class="font-bold text-slate-900">SIPEP</span>
            <span>&copy; {{ date('Y') }} SIPEP Universitas. All Rights Reserved.</span>
        </div>
        <div class="flex gap-6">
            <a class="hover:text-blue-600 transition-colors" href="#">Panduan Pengguna</a>
            <a class="hover:text-blue-600 transition-colors" href="#">Kebijakan Privasi</a>
        </div>
    </footer>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>
@stack('scripts')
</body>
</html>
