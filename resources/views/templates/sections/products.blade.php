<!-- PRODUCTS SECTION -->
<section id="products" class="py-20 lg:py-28 bg-white border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Featured Products" }}
            </h2>
            @if (!empty($content['subheading']))
                <p class="text-slate-600 mt-3 text-base">{{ $content['subheading'] }}</p>
            @endif
            <div class="h-1 w-16 bg-site-primary rounded-full mx-auto mt-4"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @if (!empty($content['items']) && is_array($content['items']))
                @foreach ($content['items'] as $prod)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">Product</span>
                                @if (!empty($prod['price']))
                                    <span class="font-extrabold text-site-primary text-sm">{{ $prod['price'] }}</span>
                                @endif
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $prod['title'] ?? $prod['name'] ?? 'Product Offering' }}</h3>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $prod['description'] ?? 'Authentic quality product crafted with precision.' }}</p>
                        </div>
                        <div class="p-6 pt-0">
                            <a href="#contact" class="w-full btn-secondary text-xs font-semibold py-2 rounded-xl text-center block">
                                Inquire Now
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>
