<!-- FOOTER SECTION -->
<footer id="footer" class="py-12 bg-slate-900 text-slate-400 border-t border-slate-800 text-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-base text-white" style="background-color: var(--color-primary);">
                    {{ strtoupper(substr($business->name, 0, 1)) }}
                </div>
                <div>
                    <span class="text-base font-bold text-white block">{{ $business->name }}</span>
                    <span class="text-xs text-slate-400 block">{{ $content['tagline'] ?? "Excellence in {$business->category}" }}</span>
                </div>
            </div>

            <div class="flex items-center gap-6 text-xs">
                @if (!empty($content['phone']))
                    <a href="tel:{{ $content['phone'] }}" class="hover:text-white transition-colors">{{ $content['phone'] }}</a>
                @endif
                @if (!empty($content['email']))
                    <a href="mailto:{{ $content['email'] }}" class="hover:text-white transition-colors">{{ $content['email'] }}</a>
                @endif
            </div>
        </div>

        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <p>{{ $content['copyright'] ?? ("© " . date('Y') . " {$business->name}. All rights reserved.") }}</p>
            <p class="flex items-center gap-1.5">
                <span>Verified Business Website Generated with</span>
                <span class="font-bold text-slate-300">AI Builds My Website</span>
            </p>
        </div>
    </div>
</footer>
