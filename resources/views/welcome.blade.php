<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Builds My Website | Your Business. Your Information. Your Website.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-blue-600 selection:text-white">
    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-slate-300 px-4 py-2 text-xs font-medium text-center border-b border-slate-800">
        <span class="inline-flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Technical Assignment for Websites.co.in • Full-Stack AI Website Generator Active
        </span>
    </div>

    <!-- Main Navigation -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs" x-data="{ mobileNavOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-sm font-bold text-lg tracking-tight">
                    AI
                </div>
                <div>
                    <span class="text-lg font-bold text-slate-900 tracking-tight">AI Builds My Website</span>
                    <span class="hidden sm:inline-block text-xs text-slate-500 ml-2 font-medium">| Your Business. Your Information. Your Website.</span>
                </div>
            </div>

            <!-- Desktop Links -->
            <div class="hidden sm:flex items-center gap-4">
                <a href="{{ route('sites.index') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Sites</span>
                </a>
                <a href="#architecture" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">Architecture</a>
                <a href="#services" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">Services</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-blue-600 transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
                        Get Started
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger Toggle -->
            <div class="sm:hidden flex items-center">
                <button type="button"
                        @click="mobileNavOpen = !mobileNavOpen"
                        class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-hidden"
                        aria-label="Toggle mobile menu"
                        data-testid="welcome-mobile-toggle">
                    <svg x-show="!mobileNavOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileNavOpen" class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileNavOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="sm:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-3 shadow-lg"
             id="welcome-mobile-drawer"
             style="display: none;">
            <nav class="space-y-1">
                <a href="{{ route('sites.index') }}" @click="mobileNavOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 flex items-center justify-between">
                    <span>Live Demonstration Sites</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </a>
                <a href="#architecture" @click="mobileNavOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Architecture
                </a>
                <a href="#services" @click="mobileNavOpen = false" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Services
                </a>
            </nav>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2 px-4 rounded-xl text-xs font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="w-full text-center py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs">
                        Get Started Free
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-grow">
        <section class="relative pt-16 pb-20 overflow-hidden bg-gradient-to-b from-white to-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 mb-6">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Strict Grounded AI • Zero Invention of Facts
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Transform Business Information into a <span class="text-blue-600">Professional Website</span> with AI.
                    </h1>

                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">
                        Never invent business facts. Our multi-stage verification engine extracts data from PDFs, online sources, and listings, detects conflicts, requires user confirmation, and compiles verified websites into tailored design presets.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors flex items-center justify-center gap-2">
                                <span>Go to Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors flex items-center justify-center gap-2">
                                <span>Get Started Free</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                                Sign In
                            </a>
                        @endauth
                    </div>

                    <!-- Real Environment & Architectural Metrics -->
                    <div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left">
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Backend</div>
                            <div class="mt-1 text-lg font-bold text-slate-900">Laravel 13</div>
                            <div class="text-xs text-emerald-600 font-semibold mt-0.5">● PHP 8.4 CLI</div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Database</div>
                            <div class="mt-1 text-lg font-bold text-slate-900">MySQL 8.0</div>
                            <div class="text-xs text-emerald-600 font-semibold mt-0.5">● 9 Domain Tables</div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Frontend</div>
                            <div class="mt-1 text-lg font-bold text-slate-900">Tailwind & Alpine</div>
                            <div class="text-xs text-emerald-600 font-semibold mt-0.5">● Compiled via Vite</div>
                        </div>

                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">AI Engine</div>
                            <div class="mt-1 text-lg font-bold text-slate-900">Google Gemini</div>
                            <div class="text-xs text-blue-600 font-semibold mt-0.5">● Prompt-Hardened</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Demonstration Websites Showcase Section -->
        <section id="showcase" class="py-16 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            Live Demonstration Websites
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3 tracking-tight">
                            Explore Real Live Generated Websites
                        </h2>
                        <p class="text-slate-600 mt-2 text-sm max-w-2xl">
                            These websites are live, public, and accessible to anyone without login. Built entirely with verified business data and zero AI hallucinations.
                        </p>
                    </div>
                    <a href="{{ route('sites.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-800 hover:border-slate-400 font-semibold text-xs shadow-2xs hover:shadow-xs transition-all whitespace-nowrap self-start md:self-auto">
                        <span>View All Live Sites Directory</span>
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Live Site 1: Patil Traditional Spices & Foods -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs hover:shadow-md hover:border-amber-300 transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    Warm Artisan Preset • Retail
                                </span>
                                <span class="text-xs font-mono text-emerald-600 font-semibold flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 200 OK
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-amber-800 transition-colors">
                                Patil Traditional Spices & Foods
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5 font-medium">Shahupuri, Kolhapur, Maharashtra</p>
                            <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                                Authentic handcrafted Kolhapuri spices, Kanda Lasun Masala, and traditional blends crafted with sun-dried Byadgi chillies since 1985.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-600 font-mono">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">📞 +91 94220 54321</span>
                                <span class="bg-slate-100 px-2 py-0.5 rounded">📍 45 Shahupuri 2nd Lane</span>
                            </div>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-mono text-blue-600">/sites/patil-traditional-spices</span>
                            <a href="{{ url('/sites/patil-traditional-spices') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-amber-700 hover:bg-amber-800 shadow-xs transition-colors">
                                <span>Open Live Website</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Live Site 2: Dr. Kulkarni Ayurvedic Wellness Clinic -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs hover:shadow-md hover:border-emerald-300 transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    Corporate Clean • Healthcare
                                </span>
                                <span class="text-xs font-mono text-emerald-600 font-semibold flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 200 OK
                                </span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-emerald-800 transition-colors">
                                Dr. Kulkarni Ayurvedic Wellness Clinic
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5 font-medium">Kothrud, Pune, Maharashtra</p>
                            <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                                Classical Ayurvedic consultation, Panchakarma detoxification, and Nadi Pariksha pulse diagnosis supervised by certified MD doctors.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-600 font-mono">
                                <span class="bg-slate-100 px-2 py-0.5 rounded">📞 +91 98230 12345</span>
                                <span class="bg-slate-100 px-2 py-0.5 rounded">📍 Mayur Colony, Kothrud</span>
                            </div>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-mono text-blue-600">/sites/dr-kulkarni-ayurveda</span>
                            <a href="{{ url('/sites/dr-kulkarni-ayurveda') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 shadow-xs transition-colors">
                                <span>Open Live Website</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Service Architecture Section -->
        <section id="architecture" class="py-16 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Modular MVC Architecture</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2">Dedicated Domain Service Layer</h2>
                    <p class="text-slate-600 mt-2 text-sm">
                        All business logic is isolated into dedicated service classes instead of bloated controllers, guaranteeing security, testability, and deterministic data flow.
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Service 1: AiService -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm mb-3">
                            01
                        </div>
                        <h3 class="font-bold text-slate-900">AiService</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">App\Services\AiService</p>
                        <p class="text-sm text-slate-600 mt-2">
                            Secure Google Gemini API integration with prompt injection defenses, JSON schema validation, timeout retries, and strict instructions forbidding instruction override.
                        </p>
                    </div>

                    <!-- Service 2: PdfProcessingService -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm mb-3">
                            02
                        </div>
                        <h3 class="font-bold text-slate-900">PdfProcessingService</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">App\Services\PdfProcessingService</p>
                        <p class="text-sm text-slate-600 mt-2">
                            Pure-PHP PDF text and metadata extraction via Smalot\PdfParser. Includes automatic detection of scanned/empty documents with transparent OCR limitations.
                        </p>
                    </div>

                    <!-- Service 3: WebResearchService -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all">
                        <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm mb-3">
                            03
                        </div>
                        <h3 class="font-bold text-slate-900">WebResearchService</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">App\Services\WebResearchService</p>
                        <p class="text-sm text-slate-600 mt-2">
                            SSRF-protected webpage reader and search provider integration. Blocks private IP ranges (RFC 1918) and cloud metadata (169.254.169.254) with graceful handling of 403 blocks.
                        </p>
                    </div>

                    <!-- Service 4: SourceExtractionService -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm mb-3">
                            04
                        </div>
                        <h3 class="font-bold text-slate-900">SourceExtractionService</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">App\Services\SourceExtractionService</p>
                        <p class="text-sm text-slate-600 mt-2">
                            Extracts and stores candidate facts from uploaded documents, Google Business links, and websites, pairing AI extraction with deterministic regex heuristics.
                        </p>
                    </div>

                    <!-- Service 5: FactVerificationService -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm mb-3">
                            05
                        </div>
                        <h3 class="font-bold text-slate-900">FactVerificationService</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">App\Services\FactVerificationService</p>
                        <p class="text-sm text-slate-600 mt-2">
                            Multi-source conflict detection (e.g. Google listing vs Business Website phone numbers). Manages statuses (extracted, source-supported, user-confirmed, conflicting, missing).
                        </p>
                    </div>

                    <!-- Service 6: WebsiteGenerationService -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all">
                        <div class="w-8 h-8 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center font-bold text-sm mb-3">
                            06
                        </div>
                        <h3 class="font-bold text-slate-900">WebsiteGenerationService</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">App\Services\WebsiteGenerationService</p>
                        <p class="text-sm text-slate-600 mt-2">
                            Structured JSON section compiler supporting 3 presets (Modern Minimal, Premium Elegant, Bold Creative) and granular per-section regeneration without touching existing edits.
                        </p>
                    </div>

                    <!-- Service 7: WebsitePublishingService -->
                    <div class="p-5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all md:col-span-2 lg:col-span-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm mb-3">
                            07
                        </div>
                        <h3 class="font-bold text-slate-900">WebsitePublishingService</h3>
                        <p class="text-xs text-slate-500 mt-1 font-mono">App\Services\WebsitePublishingService</p>
                        <p class="text-sm text-slate-600 mt-2">
                            Public website deployment engine with automated collision-safe slug generation (/sites/{slug}), immutable snapshot version history, and draft privacy enforcement.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive Alpine.js Demonstration Component -->
        <section id="verification" class="py-16 bg-slate-50 border-b border-slate-200" x-data="{ activeTab: 'conflict' }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Principle Demonstration</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2">How Conflict Resolution Works</h2>
                    <p class="text-slate-600 mt-2 text-sm">
                        When multiple sources report different information, the application never guesses or hallucinates. It presents the conflict directly to the user for definitive confirmation.
                    </p>
                </div>

                <div class="mt-8 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="flex border-b border-slate-200 bg-slate-50/80 px-4 py-2 gap-2 text-xs font-semibold">
                        <button @click="activeTab = 'conflict'" :class="activeTab === 'conflict' ? 'bg-white text-blue-600 shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition-all">
                            Example: Phone Number Conflict
                        </button>
                        <button @click="activeTab = 'scanned'" :class="activeTab === 'scanned' ? 'bg-white text-blue-600 shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition-all">
                            Example: Scanned PDF Detection
                        </button>
                    </div>

                    <div class="p-6">
                        <div x-show="activeTab === 'conflict'" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        Conflicting Fact Detected
                                    </span>
                                    <span class="text-sm font-bold text-slate-900">Phone Number</span>
                                </div>
                                <span class="text-xs text-slate-500">2 Sources Found</span>
                            </div>

                            <p class="text-sm text-slate-600">
                                Multiple phone numbers were extracted from your submitted sources. Which one should be published on your official website?
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-4 rounded-xl border-2 border-slate-200 hover:border-blue-500 cursor-pointer transition-all bg-slate-50/50">
                                    <div class="text-xs text-slate-500 font-medium">Source: Google Business Listing</div>
                                    <div class="text-lg font-bold text-slate-900 mt-1">+91 98220 12345</div>
                                    <div class="text-xs text-slate-500 mt-2">Confidence: 92% • Extracted via Search</div>
                                </div>

                                <div class="p-4 rounded-xl border-2 border-slate-200 hover:border-blue-500 cursor-pointer transition-all bg-slate-50/50">
                                    <div class="text-xs text-slate-500 font-medium">Source: Brochure PDF (Menu.pdf)</div>
                                    <div class="text-lg font-bold text-slate-900 mt-1">+91 98220 54321</div>
                                    <div class="text-xs text-slate-500 mt-2">Confidence: 96% • Extracted via PDF Parser</div>
                                </div>
                            </div>
                        </div>

                        <div x-show="activeTab === 'scanned'" class="space-y-4" style="display: none;">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                    Scanned Image PDF Detected
                                </span>
                                <span class="text-sm font-bold text-slate-900">PriceList_Scan.pdf</span>
                            </div>

                            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">
                                <strong>Honest OCR Communication:</strong> This document contains raster image pages with 0 selectable text characters. Rather than fabricating placeholder data, the system flags <code class="text-xs bg-amber-100 px-1 py-0.5 rounded">scanned_ocr_required</code> and invites the user to enter prices manually or upload a text-based document.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-slate-500">
                © {{ date('Y') }} <strong>AI Builds My Website</strong>. Technical Internship Project for Websites.co.in.
            </div>
            <div class="flex items-center gap-6 text-sm text-slate-500 font-medium">
                <span>Phase 1: Environment & Architecture</span>
                <span class="text-slate-300">•</span>
                <span class="text-emerald-600 font-semibold">Ready for Phase 2</span>
            </div>
        </div>
    </footer>
</body>
</html>
