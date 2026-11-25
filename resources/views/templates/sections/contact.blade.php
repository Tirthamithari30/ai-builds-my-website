<!-- CONTACT SECTION -->
<section id="contact" class="py-20 lg:py-28 bg-white border-b border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-16">
            @if (!empty($content['badge']))
                <span class="text-xs font-bold uppercase tracking-wider text-site-primary block mb-2">{{ $content['badge'] }}</span>
            @endif
            <h2 class="text-3xl sm:text-4xl font-extrabold text-site-secondary tracking-tight">
                {{ $content['heading'] ?? "Get In Touch Directly" }}
            </h2>
            <div class="h-1 w-16 bg-site-primary rounded-full mt-4"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Left Info Cards -->
            <div class="lg:col-span-5 space-y-6">
                <p class="text-base text-slate-600 leading-relaxed">
                    Have questions about our {{ str_replace('_', ' ', $business->category) }} services or want to place an order? Contact us using the confirmed details below.
                </p>

                <div class="space-y-4">
                    @if (!empty($content['phone']))
                        <a href="tel:{{ $content['phone'] }}" class="flex items-center gap-4 p-4 rounded-2xl border border-slate-200/80 bg-slate-50/60 hover:border-slate-300 transition-colors block group">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white flex-shrink-0 group-hover:scale-105 transition-transform" style="background-color: var(--color-primary);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Phone Number</div>
                                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $content['phone'] }}</div>
                            </div>
                        </a>
                    @endif

                    @if (!empty($content['email']))
                        <a href="mailto:{{ $content['email'] }}" class="flex items-center gap-4 p-4 rounded-2xl border border-slate-200/80 bg-slate-50/60 hover:border-slate-300 transition-colors block group">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white flex-shrink-0 group-hover:scale-105 transition-transform" style="background-color: var(--color-primary);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Email Address</div>
                                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $content['email'] }}</div>
                            </div>
                        </a>
                    @endif

                    @if (!empty($content['address']))
                        <div class="flex items-center gap-4 p-4 rounded-2xl border border-slate-200/80 bg-slate-50/60">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white flex-shrink-0" style="background-color: var(--color-primary);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Address</div>
                                <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $content['address'] }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Interactive Form Representation -->
            <div class="lg:col-span-7 bg-slate-50/80 p-8 rounded-2xl border border-slate-200 shadow-xs">
                <h3 class="text-lg font-bold text-slate-900 mb-6">{{ $content['form_title'] ?? 'Send Us a Direct Message' }}</h3>
                <form onsubmit="event.preventDefault(); alert('Inquiry submitted successfully! The business owner will contact you.');" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Your Name</label>
                            <input type="text" required placeholder="John Doe" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 bg-white focus:outline-hidden focus:ring-2 focus:ring-site-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Your Email</label>
                            <input type="email" required placeholder="john@example.com" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 bg-white focus:outline-hidden focus:ring-2 focus:ring-site-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Subject</label>
                        <input type="text" placeholder="Inquiry regarding {{ $business->name }}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 bg-white focus:outline-hidden focus:ring-2 focus:ring-site-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Your Message</label>
                        <textarea rows="4" required placeholder="Tell us how we can help you..." class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 bg-white focus:outline-hidden focus:ring-2 focus:ring-site-primary"></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full py-3 rounded-xl font-bold text-sm shadow-xs">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
