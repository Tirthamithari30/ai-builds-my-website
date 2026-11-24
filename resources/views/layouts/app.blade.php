<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'AI Builds My Website' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-blue-600 selection:text-white">
    <!-- Top Global App Navbar -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-xs font-bold text-base">
                        AI
                    </div>
                    <span class="text-base font-bold text-slate-900 tracking-tight hidden sm:inline-block">AI Builds My Website</span>
                </a>

                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ request()->routeIs('dashboard*') ? 'bg-slate-100 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }} transition-colors">
                        Dashboard
                    </a>
                    <a href="{{ route('businesses.create') }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ request()->routeIs('businesses.create') ? 'bg-slate-100 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }} transition-colors">
                        New Project
                    </a>
                </nav>
            </div>

            <!-- User Menu & Action Button -->
            <div class="flex items-center gap-3">
                <a href="{{ route('businesses.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Create Website</span>
                </a>

                <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-900 leading-tight">{{ Auth::user()->name }}</span>
                        <span class="text-[11px] text-slate-500 leading-tight">{{ Auth::user()->email }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Log Out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-4">
        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-emerald-800 text-sm shadow-xs" role="alert">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-between text-rose-800 text-sm shadow-xs" role="alert">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Please fix the following issues:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Body -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Dashboard Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <div>
                © {{ date('Y') }} <strong>AI Builds My Website</strong> • Technical Internship Assignment for Websites.co.in
            </div>
            <div>
                Built with Laravel, MySQL, Tailwind CSS & Alpine.js
            </div>
        </div>
    </footer>
</body>
</html>
