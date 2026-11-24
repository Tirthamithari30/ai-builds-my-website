<!-- FAQ SECTION -->
<section id="faq" class="py-20 lg:py-28 bg-white border-b border-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Frequently Asked Questions" }}
            </h2>
            <div class="h-1 w-16 bg-site-primary rounded-full mx-auto mt-4"></div>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            @if (!empty($content['questions']) && is_array($content['questions']))
                @foreach ($content['questions'] as $idx => $faq)
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                        <button type="button"
                                @click="active = (active === {{ $idx }}) ? null : {{ $idx }}"
                                class="w-full text-left px-6 py-5 flex items-center justify-between font-bold text-slate-900 text-base hover:bg-slate-50 transition-colors">
                            <span>{{ $faq['q'] ?? 'Question' }}</span>
                            <svg class="w-5 h-5 text-slate-400 transform transition-transform"
                                 :class="{ 'rotate-180': active === {{ $idx }} }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="active === {{ $idx }}"
                             x-collapse
                             class="px-6 pb-6 text-sm text-slate-600 leading-relaxed border-t border-slate-100 bg-slate-50/50">
                            {{ $faq['a'] ?? 'Answer details.' }}
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
