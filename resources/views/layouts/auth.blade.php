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
                'secondary-fixed':            '#89f5e7',
                'error':                      '#ba1a1a',
                'error-container':            '#ffdad6',
                'on-error':                   '#ffffff',
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
            borderRadius: { DEFAULT: '0.25rem', lg: '0.5rem', xl: '0.75rem', full: '9999px' },
            spacing: { xs: '4px', sm: '8px', md: '16px', lg: '24px', xl: '32px', '2xl': '48px', gutter: '24px', base: '4px' },
            fontFamily: {
                'h1': ['"Public Sans"'], 'h2': ['"Public Sans"'], 'h3': ['"Public Sans"'],
                'body-md': ['"Public Sans"'], 'body-sm': ['"Public Sans"'],
                'label-md': ['"Public Sans"'], 'label-sm': ['"Public Sans"'],
            },
            fontSize: {
                'h1':       ['36px', { lineHeight: '1.2', fontWeight: '700' }],
                'h2':       ['30px', { lineHeight: '1.3', fontWeight: '600' }],
                'h3':       ['24px', { lineHeight: '1.3', fontWeight: '600' }],
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
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        vertical-align: middle;
    }
    .bg-overlay {
        background-image: linear-gradient(rgba(13,28,46,0.85), rgba(13,28,46,0.85)),
            url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1920&q=80');
        background-size: cover;
        background-position: center;
    }
    .bg-institutional {
        background-color: #f8f9ff;
        background-image: radial-gradient(circle at 2px 2px, #d5e3fc 1px, transparent 0);
        background-size: 40px 40px;
    }
</style>
</head>
<body class="font-body-md text-on-background antialiased min-h-screen flex flex-col @yield('body-class', '')">

@yield('body')

<footer class="relative z-10 w-full py-lg px-2xl flex flex-col md:flex-row justify-between items-center gap-4 @yield('footer-class', 'bg-slate-50 border-t border-slate-200 text-slate-500')">
    <div class="flex items-center gap-2">
        <span class="font-bold text-xs">SIPEP</span>
        <span class="text-xs">&copy; {{ date('Y') }} SIPEP. All Rights Reserved.</span>
    </div>
    <div class="flex gap-6">
        <a class="text-xs hover:underline" href="#">Panduan Pengguna</a>
        <a class="text-xs hover:underline" href="#">Kebijakan Privasi</a>
        <a class="text-xs hover:underline" href="#">Kontak</a>
    </div>
</footer>

@stack('scripts')
</body>
</html>
