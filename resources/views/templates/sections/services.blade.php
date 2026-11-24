<!-- SERVICES SECTION -->
<section id="services" class="py-20 lg:py-28 {{ $preset['key'] === 'premium_elegant' ? 'bg-[#fdfbf7]' : 'bg-slate-50/70' }} border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Our Services & Capabilities" }}
            </h2>
            @if (!empty($content['subheading']))
                <p class="text-slate-600 mt-3 text-base">{{ $content['subheading'] }}</p>
            @endif
            <div class="h-1 w-16 bg-site-primary rounded-full mx-auto mt-4"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @if (!empty($content['items']) && is_array($content['items']))
                @foreach ($content['items'] as $item)
                    <div class="bg-white p-7 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white mb-5 shadow-2xs group-hover:scale-105 transition-transform" style="background-color: var(--color-primary);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $item['title'] ?? 'Specialized Service' }}</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $item['description'] ?? 'Tailored service designed for exceptional results.' }}</p>
                        </div>
                        @if (!empty($item['price']))
                            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-500">Pricing</span>
                                <span class="font-bold text-slate-900">{{ $item['price'] }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
