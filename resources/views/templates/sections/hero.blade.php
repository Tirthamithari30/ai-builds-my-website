<!-- HERO SECTION -->
<section id="hero" class="relative overflow-hidden py-20 lg:py-32 {{ $preset['key'] === 'premium_elegant' ? 'bg-[#fdfbf7]' : ($preset['key'] === 'bold_creative' ? 'bg-gradient-to-b from-purple-50/60 to-white' : 'bg-slate-50/60') }} border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                @if (!empty($content['badge']))
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold tracking-wide uppercase shadow-2xs {{ $preset['key'] === 'premium_elegant' ? 'bg-amber-100/70 text-amber-900 border border-amber-300' : 'bg-site-primary/10 text-site-primary' }}">
                        <span class="w-2 h-2 rounded-full bg-current"></span>
                        <span>{{ $content['badge'] }}</span>
                    </div>
                @endif

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-site-secondary leading-[1.12]">
                    {{ $content['heading'] ?? "Welcome to {$business->name}" }}
                </h1>

                <p class="text-lg sm:text-xl text-slate-600 leading-relaxed font-normal max-w-2xl">
                    {{ $content['subheading'] ?? $business->description }}
                </p>

                <div class="pt-4 flex flex-wrap items-center gap-4">
                    <a href="{{ $content['primary_button_link'] ?? '#contact' }}" class="btn-primary text-sm font-bold px-7 py-3.5 rounded-xl shadow-sm inline-flex items-center gap-2">
                        <span>{{ $content['primary_button_text'] ?? 'Get in Touch' }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>

                    @if (!empty($content['secondary_button_text']))
                        <a href="{{ $content['secondary_button_link'] ?? '#services' }}" class="btn-secondary text-sm font-bold px-7 py-3.5 rounded-xl inline-flex items-center gap-2">
                            <span>{{ $content['secondary_button_text'] }}</span>
                        </a>
                    @endif
                </div>

                @if (!empty($website->settings['city']))
                    <div class="pt-6 flex items-center gap-6 text-xs text-slate-500 font-medium">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Verified Business in {{ $website->settings['city'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>100% Fact Grounded</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-5">
                @php
                    $categoryFallback = match(strtolower($business->category ?? '')) {
                        'restaurant', 'cafe', 'food', 'retail' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1200&q=80',
                        'salon', 'spa', 'wellness', 'fitness', 'healthcare', 'professional_services' => 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?auto=format&fit=crop&w=1200&q=80',
                        default => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                    };
                    $heroImageUrl = !empty($content['hero_image']) ? $content['hero_image'] : $categoryFallback;
                @endphp
                <div class="relative mx-auto max-w-md lg:max-w-none">
                    <div class="aspect-4/3 rounded-2xl overflow-hidden shadow-2xl border-4 border-white bg-slate-100">
                        <img src="{{ $heroImageUrl }}"
                             alt="{{ $business->name }}"
                             class="w-full h-full object-cover">
                    </div>
                    @if (!empty($content['badge']))
                        <div class="absolute -bottom-5 -left-5 bg-white p-4 rounded-xl shadow-lg border border-slate-100 hidden sm:flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold" style="background-color: var(--color-primary);">
                                ✓
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">{{ ucwords(str_replace('_', ' ', $business->category)) }}</div>
                                <div class="text-[11px] text-slate-500">Authentic & Verified</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
