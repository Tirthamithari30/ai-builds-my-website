<!-- GALLERY SECTION -->
<section id="gallery" class="py-20 lg:py-28 bg-white border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Visual Showcase" }}
            </h2>
            <div class="h-1 w-16 bg-site-primary rounded-full mx-auto mt-4"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @if (!empty($content['images']) && is_array($content['images']))
                @foreach ($content['images'] as $img)
                    <div class="group relative rounded-2xl overflow-hidden aspect-4/3 bg-slate-100 border border-slate-200 shadow-xs">
                        <img src="{{ $img['url'] }}"
                             alt="{{ $img['caption'] ?? $business->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @if (!empty($img['caption']))
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                                <span class="text-xs font-semibold text-white tracking-wide">{{ $img['caption'] }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
