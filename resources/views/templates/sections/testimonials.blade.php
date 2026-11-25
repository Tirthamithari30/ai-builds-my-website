<!-- TESTIMONIALS SECTION -->
<section id="testimonials" class="py-20 lg:py-28 {{ $preset['key'] === 'premium_elegant' ? 'bg-[#fdfbf7]' : ($preset['key'] === 'bold_creative' ? 'bg-purple-50/50' : 'bg-slate-50/60') }} border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Client Testimonials" }}
            </h2>
            @if (!empty($content['subheading']))
                <p class="text-slate-600 mt-3 text-base">{{ $content['subheading'] }}</p>
            @endif
            <div class="h-1 w-16 bg-site-primary rounded-full mx-auto mt-4"></div>
        </div>

        @php
            $testimonialItems = $content['items'] ?? $content['reviews'] ?? $content['testimonials'] ?? [];
        @endphp

        @if (!empty($testimonialItems) && is_array($testimonialItems))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($testimonialItems as $item)
                    <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-slate-300 transition-colors">
                        <div>
                            <!-- Star Rating -->
                            <div class="flex items-center gap-1 text-amber-400 mb-4">
                                @for ($i = 0; $i < ($item['rating'] ?? 5); $i++)
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <blockquote class="text-sm text-slate-700 leading-relaxed italic">
                                "{{ $item['quote'] ?? $item['comment'] ?? $item['text'] ?? 'Authentic and dependable service.' }}"
                            </blockquote>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold text-slate-900">{{ $item['author'] ?? $item['name'] ?? 'Verified Client' }}</div>
                                @if (!empty($item['role']) || !empty($item['city']))
                                    <div class="text-[11px] text-slate-500">{{ $item['role'] ?? $item['city'] }}</div>
                                @endif
                            </div>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                Verified
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 rounded-2xl bg-white border border-slate-200 text-center max-w-md mx-auto text-sm text-slate-500">
                No verified customer reviews have been added yet.
            </div>
        @endif
    </div>
</section>
