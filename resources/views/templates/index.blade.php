<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $website->title }} — {{ $website->settings['tagline'] ?? $business->name }}</title>
    <meta name="description" content="{{ $website->meta_description }}">

    <!-- Google Fonts for Selected Preset -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-primary: {{ $website->primary_color }};
            --color-secondary: {{ $website->secondary_color }};
            --site-font: {!! $website->font_family !!};
        }

        body {
            font-family: var(--site-font);
        }

        .bg-site-primary { background-color: var(--color-primary); }
        .text-site-primary { color: var(--color-primary); }
        .border-site-primary { border-color: var(--color-primary); }
        .bg-site-secondary { background-color: var(--color-secondary); }
        .text-site-secondary { color: var(--color-secondary); }

        .btn-primary {
            background-color: var(--color-primary);
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            filter: brightness(0.92);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: transparent;
            border: 2px solid var(--color-primary);
            color: var(--color-primary);
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background-color: var(--color-primary);
            color: #ffffff;
        }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-slate-900 selection:text-white">

    <!-- Top Sticky Header / Navigation -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-2xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="#hero" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg text-white shadow-xs" style="background-color: var(--color-primary);">
                    {{ strtoupper(substr($business->name, 0, 1)) }}
                </div>
                <div>
                    <span class="text-lg font-bold tracking-tight block leading-tight text-site-secondary">{{ $website->title }}</span>
                    @if (!empty($website->settings['city']))
                        <span class="text-xs text-slate-500 block">{{ $website->settings['city'] }}</span>
                    @endif
                </div>
            </a>

            <!-- Quick Section Navigation -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                @foreach ($sections as $s)
                    @if (in_array($s->section_type, ['about', 'services', 'products', 'features', 'gallery', 'location', 'contact']))
                        <a href="#{{ $s->section_type }}" class="hover:text-site-primary transition-colors capitalize">
                            {{ $s->section_type }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- Header Action Button -->
            <div class="flex items-center gap-3">
                @if (!empty($website->settings['phone']))
                    <a href="tel:{{ $website->settings['phone'] }}" class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>{{ $website->settings['phone'] }}</span>
                    </a>
                @endif
                <a href="#contact" class="btn-primary text-xs font-semibold px-4 py-2 rounded-xl shadow-xs">
                    Contact Us
                </a>
            </div>
        </div>
    </header>

    <!-- Render Each Configured Section -->
    <main>
        @foreach ($sections as $section)
            @php
                $partialPath = 'templates.sections.' . $section->section_type;
                $content = $section->content ?? [];
            @endphp

            @if (view()->exists($partialPath))
                @include($partialPath, [
                    'section' => $section,
                    'content' => $content,
                    'business' => $business,
                    'website' => $website,
                    'preset' => $preset,
                ])
            @else
                <!-- Fallback generic section -->
                <section id="{{ $section->section_type }}" class="py-16 px-4 max-w-6xl mx-auto border-b border-slate-100">
                    <h2 class="text-2xl font-bold mb-4 text-site-secondary">{{ $content['heading'] ?? ucwords(str_replace('_', ' ', $section->section_type)) }}</h2>
                    <p class="text-slate-600">{{ $content['content'] ?? 'Verified information tailored for ' . $business->name . '.' }}</p>
                </section>
            @endif
        @endforeach
    </main>

</body>
</html>
