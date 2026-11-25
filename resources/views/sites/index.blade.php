<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Published Websites Directory | AI Builds My Website</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-slate-300 px-4 py-2 text-xs font-medium text-center border-b border-slate-800">
        <span class="inline-flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Public Directory • All websites verified and generated with zero hallucination
        </span>
    </div>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-sm font-bold text-lg tracking-tight group-hover:scale-105 transition-transform">
                    AI
                </div>
                <div>
                    <span class="text-lg font-bold text-slate-900 tracking-tight">AI Builds My Website</span>
                    <span class="hidden sm:inline-block text-xs text-slate-500 ml-2 font-medium">| Public Showcase</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                    Home
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-blue-600 transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
                        Build Your Website
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Header -->
    <section class="py-12 bg-white border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 mb-4">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Public Showcase Directory
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Live Published Websites
            </h1>
            <p class="mt-4 text-base text-slate-600 leading-relaxed">
                Explore real, authentic commercial websites generated through the platform. Every site features verified business data, responsive mobile navigation, and single-click direct contact channels.
            </p>
        </div>
    </section>

    <!-- Published Sites Grid -->
    <main class="flex-1 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($websites->isEmpty())
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-200/80 max-w-md mx-auto p-8 shadow-xs">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">No websites published yet</h3>
                    <p class="text-xs text-slate-500 mt-1">Check back soon or sign in to generate and publish your own website!</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    @foreach ($websites as $site)
                        @php
                            $biz = $site->business;
                            $publicUrl = url('/sites/' . $site->slug);
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs hover:shadow-md hover:border-slate-300 transition-all flex flex-col justify-between group">
                            <!-- Card Header -->
                            <div class="p-6 sm:p-8">
                                <div class="flex items-center justify-between gap-4 mb-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Live Published
                                    </span>
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 capitalize">
                                        {{ str_replace('_', ' ', $biz->category ?? 'Business') }}
                                    </span>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors">
                                    <a href="{{ $publicUrl }}" target="_blank">
                                        {{ $site->title }}
                                    </a>
                                </h2>

                                @if (!empty($site->settings['tagline']))
                                    <p class="text-xs font-medium text-slate-500 mt-1 italic">
                                        "{{ $site->settings['tagline'] }}"
                                    </p>
                                @endif

                                <p class="text-sm text-slate-600 mt-4 leading-relaxed line-clamp-3">
                                    {{ $site->meta_description ?? $biz->description ?? 'Verified commercial website with grounded facts.' }}
                                </p>

                                <!-- Key Meta Badges -->
                                <div class="mt-6 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                    @if (!empty($biz->city))
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $biz->city }}{{ !empty($biz->state) ? ', ' . $biz->state : '' }}</span>
                                        </div>
                                    @endif

                                    @if (!empty($biz->phone))
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            <span>{{ $biz->phone }}</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-1 font-mono text-[11px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">
                                        /sites/{{ $site->slug }}
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer Action -->
                            <div class="px-6 sm:px-8 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs text-slate-500">Design Preset: <strong class="text-slate-700 capitalize">{{ str_replace('_', ' ', $site->design_preset) }}</strong></span>
                                <a href="{{ $publicUrl }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                                    <span>Visit Live Website</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-8 border-t border-slate-800 text-xs text-center">
        <p>© {{ date('Y') }} AI Builds My Website. All verified websites generated with strict fact-grounding.</p>
    </footer>

</body>
</html>
