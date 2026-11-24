<x-app-layout :title="'Website Preview — ' . $website->title">
    <div class="min-h-[calc(100vh-4rem)] flex flex-col bg-slate-900"
         x-data="{
             device: 'desktop',
             inspectorOpen: false,
             getContainerClass() {
                 if (this.device === 'mobile') {
                     return 'w-[375px] h-[720px] rounded-[36px] border-[10px] border-slate-800 shadow-2xl ring-1 ring-white/10 my-6 transition-all duration-300';
                 }
                 if (this.device === 'tablet') {
                     return 'w-[768px] h-[820px] rounded-2xl border-8 border-slate-800 shadow-2xl ring-1 ring-white/10 my-6 transition-all duration-300';
                 }
                 return 'w-full h-[calc(100vh-10rem)] shadow-lg transition-all duration-300';
             }
         }">

        <!-- Top Preview Navigation & Device Switcher Bar -->
        <div class="bg-slate-950/90 backdrop-blur-md border-b border-slate-800 px-4 sm:px-6 py-3 flex flex-wrap items-center justify-between gap-4 sticky top-16 z-30">
            <!-- Left Info & Breadcrumbs -->
            <div class="flex items-center gap-3">
                <a href="{{ route('businesses.show', $business) }}"
                   class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-colors"
                   title="Back to Business Dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>

                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-white tracking-tight">{{ $website->title }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                            v{{ $website->current_version }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 capitalize">
                            {{ str_replace('_', ' ', $website->design_preset) }}
                        </span>
                        @if ($website->settings['ai_generated'] ?? false)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                Gemini AI
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-700 text-slate-300">
                                Verified Engine
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-400">
                        {{ $sections->count() }} active sections • {{ $business->name }}
                    </div>
                </div>
            </div>

            <!-- Center Responsive Viewport Controls -->
            <div class="flex items-center bg-slate-900 p-1 rounded-xl border border-slate-800 shadow-inner">
                <!-- Desktop Button -->
                <button type="button" @click="device = 'desktop'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                        :class="device === 'desktop' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Desktop</span>
                </button>

                <!-- Tablet Button -->
                <button type="button" @click="device = 'tablet'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                        :class="device === 'tablet' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Tablet (768px)</span>
                </button>

                <!-- Mobile Button -->
                <button type="button" @click="device = 'mobile'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                        :class="device === 'mobile' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Mobile (375px)</span>
                </button>
            </div>

            <!-- Right Actions: Regenerate, Versions, Inspector -->
            <div class="flex items-center gap-2">
                <button type="button" @click="inspectorOpen = !inspectorOpen"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors flex items-center gap-1.5"
                        :class="inspectorOpen ? 'bg-slate-800 border-slate-600 text-white' : 'border-slate-800 text-slate-400 hover:text-white hover:bg-slate-900'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    <span class="hidden md:inline">Sections</span>
                </button>

                <a href="{{ route('websites.versions', $website) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Versions ({{ $versions->count() }})</span>
                </a>

                <a href="{{ route('websites.edit', $website) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Edit Website</span>
                </a>

                <a href="{{ route('businesses.website.setup', $business) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Regenerate</span>
                </a>
            </div>
        </div>

        <!-- Main Preview Canvas & Side Drawer -->
        <div class="flex-1 flex overflow-hidden relative">
            <!-- Central Viewport Display -->
            <div class="flex-1 flex items-center justify-center p-2 sm:p-6 overflow-y-auto bg-slate-950/40">
                <div :class="getContainerClass()" class="overflow-hidden bg-white">
                    <iframe src="{{ route('websites.preview.frame', $website) }}"
                            title="Website Preview Frame"
                            class="w-full h-full border-0"></iframe>
                </div>
            </div>

            <!-- Collapsible Section Inspector Drawer -->
            <div x-show="inspectorOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 style="display: none;"
                 class="w-80 bg-slate-950 border-l border-slate-800 text-slate-300 flex flex-col z-20">

                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-white uppercase tracking-wider">Included Sections</h2>
                        <span class="text-[11px] text-slate-500">{{ $sections->count() }} active sections</span>
                    </div>
                    <button type="button" @click="inspectorOpen = false" class="text-slate-500 hover:text-white">
                        ✕
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3 text-xs">
                    @foreach ($sections as $sec)
                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white capitalize">{{ $sec->section_type }}</span>
                                <span class="text-[10px] text-emerald-400 font-semibold bg-emerald-950 px-1.5 py-0.5 rounded">Visible</span>
                            </div>
                            <div class="text-[11px] text-slate-400 truncate">
                                {{ $sec->content['heading'] ?? $sec->content['title'] ?? 'Section content' }}
                            </div>

                            @if (!empty($sec->design_overrides['fact_references']))
                                <div class="pt-1 flex flex-wrap gap-1">
                                    @foreach ($sec->design_overrides['fact_references'] as $ref)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-slate-800 text-slate-300 border border-slate-700">
                                            #{{ $ref }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
