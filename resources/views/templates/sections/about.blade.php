<!-- ABOUT SECTION -->
<section id="about" class="py-20 lg:py-28 bg-white border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "About {$business->name}" }}
            </h2>
            <div class="h-1 w-16 bg-site-primary rounded-full mt-4"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <div class="lg:col-span-7 space-y-5 text-slate-600 text-base leading-relaxed">
                <p>{{ $content['description_p1'] ?? $business->description }}</p>
                @if (!empty($content['description_p2']))
                    <p>{{ $content['description_p2'] }}</p>
                @endif
            </div>

            <div class="lg:col-span-5 space-y-4">
                @if (!empty($content['highlights']) && is_array($content['highlights']))
                    @foreach ($content['highlights'] as $hl)
                        <div class="p-5 rounded-2xl border border-slate-200/80 bg-slate-50/60 shadow-2xs hover:border-slate-300 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background-color: var(--color-primary);">
                                    ✓
                                </div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $hl['title'] ?? 'Core Value' }}</h3>
                            </div>
                            <p class="text-xs text-slate-600 mt-2 pl-11">{{ $hl['desc'] ?? '' }}</p>
                        </div>
                    @endforeach
                @else
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-sm text-slate-600">
                        <div class="font-bold text-slate-900 mb-2">Our Quality Commitment</div>
                        <p>We pride ourselves on dependable delivery, clear communication, and customer satisfaction.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
