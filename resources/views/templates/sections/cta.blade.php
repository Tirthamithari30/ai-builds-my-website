<!-- CTA (CALL TO ACTION) SECTION -->
<section id="cta" class="py-20 lg:py-28 {{ $preset['key'] === 'premium_elegant' ? 'bg-[#1c1917] text-white' : ($preset['key'] === 'bold_creative' ? 'bg-gradient-to-r from-purple-900 to-indigo-900 text-white' : 'bg-slate-900 text-white') }} relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 space-y-6">
        <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight">
            {{ $content['heading'] ?? "Experience Excellence with {$business->name}" }}
        </h2>
        <p class="text-lg text-slate-300 max-w-2xl mx-auto font-normal">
            {{ $content['subheading'] ?? "Reach out directly to discover how our verified {$business->category} offerings can benefit you today." }}
        </p>
        <div class="pt-4">
            <a href="{{ $content['button_link'] ?? '#contact' }}"
               class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-bold text-sm shadow-xl transition-all hover:scale-105"
               style="background-color: var(--color-primary); color: #ffffff;">
                <span>{{ $content['button_text'] ?? 'Get in Touch Today' }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
