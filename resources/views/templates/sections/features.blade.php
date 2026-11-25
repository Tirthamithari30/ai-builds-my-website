<!-- FEATURES / WHY CHOOSE US SECTION -->
<section id="features" class="py-20 lg:py-28 {{ $preset['key'] === 'premium_elegant' ? 'bg-[#fdfbf7]' : ($preset['key'] === 'bold_creative' ? 'bg-purple-50/40' : 'bg-slate-50/60') }} border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Why Clients Choose {$business->name}" }}
            </h2>
            @if (!empty($content['subheading']))
                <p class="text-slate-600 mt-3 text-base">{{ $content['subheading'] }}</p>
            @endif
            <div class="h-1 w-16 bg-site-primary rounded-full mx-auto mt-4"></div>
        </div>

        @php
            $featureCards = $content['cards'] ?? $content['features'] ?? $content['items'] ?? [];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @if (!empty($featureCards) && is_array($featureCards))
                @foreach ($featureCards as $card)
                    <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-colors">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white mb-6 shadow-xs" style="background-color: var(--color-primary);">
                            @if (($card['icon'] ?? '') === 'clock')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif (($card['icon'] ?? '') === 'shield')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $card['title'] ?? $card['name'] ?? 'Verified Feature' }}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $card['desc'] ?? $card['description'] ?? '' }}</p>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
