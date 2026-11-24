<!-- LOCATION & HOURS SECTION -->
<section id="location" class="py-20 lg:py-28 {{ $preset['key'] === 'premium_elegant' ? 'bg-[#fdfbf7]' : 'bg-slate-50/70' }} border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Visit Us" }}
            </h2>
            <div class="h-1 w-16 bg-site-primary rounded-full mt-4"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Physical Location Details -->
            <div class="lg:col-span-6 space-y-6 bg-white p-8 rounded-2xl border border-slate-200 shadow-xs">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Physical Location</h3>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white flex-shrink-0 mt-0.5" style="background-color: var(--color-primary);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-base font-bold text-slate-900 leading-snug">{{ $content['address'] ?? ($business->address ?: 'Physical address available on request') }}</p>
                            @if (!empty($content['city']))
                                <p class="text-xs text-slate-500 mt-1">{{ $content['city'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Operating Hours</h3>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white flex-shrink-0 mt-0.5" style="background-color: var(--color-primary);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 leading-relaxed">{{ $content['operating_hours'] ?? 'Monday – Saturday: 9:00 AM – 8:00 PM' }}</p>
                            <p class="text-xs text-slate-500 mt-1">Authentic business schedule confirmed by owner.</p>
                        </div>
                    </div>
                </div>

                @if (!empty($content['map_note']))
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
                        <svg class="w-4 h-4 text-site-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $content['map_note'] }}</span>
                    </div>
                @endif
            </div>

            <!-- Stylized Location Map Representation -->
            <div class="lg:col-span-6">
                <div class="h-80 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-300 relative flex items-center justify-center p-6 text-center shadow-xs overflow-hidden">
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#000_1px,transparent_1px)] [background-size:16px_16px]"></div>
                    <div class="relative z-10 max-w-sm space-y-3">
                        <div class="w-12 h-12 rounded-full mx-auto flex items-center justify-center text-white shadow-md animate-bounce" style="background-color: var(--color-primary);">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-slate-900 text-base">{{ $business->name }}</h4>
                        <p class="text-xs text-slate-600">{{ $content['address'] ?? ($business->city ? "Located in {$business->city}" : "Contact us for precise directions") }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
