<x-app-layout :title="'Website Editor — ' . $website->title">
    <div class="h-[calc(100vh-4rem)] flex flex-col bg-slate-900 overflow-hidden"
         x-data="{
             activeTab: 'sections',
             device: 'desktop',
             selectedSectionId: {{ $sections->first()?->id ?? 'null' }},
             saving: false,
             saveStatus: 'All changes saved',
             addSectionModalOpen: false,
             regenModalOpen: false,
             regenerating: false,
             regenSectionId: null,
             regenSectionTitle: '',
             regenUserPrompt: '',
             regenOriginal: null,
             regenProposed: null,
             imageModalOpen: false,
             activeImageField: null,
             newSectionType: 'about',

             getContainerClass() {
                 if (this.device === 'mobile') {
                     return 'w-[375px] h-[720px] rounded-[36px] border-[10px] border-slate-800 shadow-2xl ring-1 ring-white/10 my-4 transition-all duration-300';
                 }
                 if (this.device === 'tablet') {
                     return 'w-[768px] h-[820px] rounded-2xl border-8 border-slate-800 shadow-2xl ring-1 ring-white/10 my-4 transition-all duration-300';
                 }
                 return 'w-full h-full shadow-lg transition-all duration-300';
             },

             refreshPreview() {
                 const iframe = document.getElementById('preview-iframe');
                 if (iframe) {
                     iframe.src = iframe.src;
                 }
             },

             openRegenModal(secId, title) {
                 this.regenSectionId = secId;
                 this.regenSectionTitle = title;
                 this.regenUserPrompt = '';
                 this.regenOriginal = null;
                 this.regenProposed = null;
                 this.regenModalOpen = true;
             },

             async requestRegenPreview() {
                 this.regenerating = true;
                 try {
                     const res = await fetch(`/sections/${this.regenSectionId}/regenerate-preview`, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                         },
                         body: JSON.stringify({ user_prompt: this.regenUserPrompt })
                     });
                     const data = await res.json();
                     if (data.success) {
                         this.regenOriginal = data.original_content;
                         this.regenProposed = data.proposed_content;
                     } else {
                         alert(data.error || 'Regeneration failed');
                     }
                 } catch (e) {
                     alert('Error calling AI service: ' + e.message);
                 } finally {
                     this.regenerating = false;
                 }
             },

             async acceptRegen() {
                 if (!this.regenProposed) return;
                 try {
                     const res = await fetch(`/sections/${this.regenSectionId}/apply-regeneration`, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                         },
                         body: JSON.stringify({ content: this.regenProposed })
                     });
                     const data = await res.json();
                     if (data.success) {
                         this.regenModalOpen = false;
                         window.location.reload();
                     }
                 } catch (e) {
                     alert('Failed to apply regeneration: ' + e.message);
                 }
             },

             selectImage(url) {
                 if (this.activeImageField) {
                     this.activeImageField.value = url;
                     this.activeImageField.dispatchEvent(new Event('input'));
                 }
                 this.imageModalOpen = false;
             }
         }">

        <!-- Top Editor Navbar & Toolbar -->
        <header class="bg-slate-950/95 backdrop-blur-md border-b border-slate-800 px-4 sm:px-6 h-14 flex items-center justify-between gap-4 z-30 flex-shrink-0">
            <!-- Left Info -->
            <div class="flex items-center gap-3">
                <a href="{{ route('businesses.show', $business) }}"
                   class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                   title="Back to Business Project">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>

                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-white tracking-tight truncate max-w-xs">{{ $website->title }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        Editor v{{ $website->current_version }}
                    </span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border transition-colors"
                          :class="saving ? 'bg-amber-950/60 text-amber-300 border-amber-800' : 'bg-emerald-950/60 text-emerald-400 border-emerald-900'">
                        <svg x-show="saving" class="w-3 h-3 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24" style="display: none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-show="!saving" class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span x-text="saving ? 'Saving changes...' : saveStatus">All changes saved</span>
                    </span>
                </div>
            </div>

            <!-- Center Viewport Switcher -->
            <div class="hidden sm:flex items-center bg-slate-900 p-1 rounded-xl border border-slate-800 shadow-inner">
                <button type="button" @click="device = 'desktop'"
                        class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all"
                        :class="device === 'desktop' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Desktop</span>
                </button>

                <button type="button" @click="device = 'tablet'"
                        class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all"
                        :class="device === 'tablet' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Tablet</span>
                </button>

                <button type="button" @click="device = 'mobile'"
                        class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all"
                        :class="device === 'mobile' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Mobile</span>
                </button>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2">
                <a href="{{ route('websites.preview', $website) }}" target="_blank"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>Preview Live</span>
                </a>

                <a href="{{ route('websites.versions', $website) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Versions</span>
                </a>

                <button type="button" @click="refreshPreview()"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Refresh Preview</span>
                </button>

                @if ($website->is_published)
                    <a href="{{ url('/sites/' . $website->slug) }}" target="_blank"
                       class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600/30 text-emerald-300 border border-emerald-500/50 hover:bg-emerald-600/40 transition-colors shadow-xs flex items-center gap-1.5"
                       title="Open Live Public Website">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Live Site</span>
                    </a>

                    <form method="POST" action="{{ route('websites.unpublish', $website) }}"
                          onsubmit="return confirm('Take website offline? Visitors will receive a 404 until republished.');"
                          class="inline">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-600/20 text-rose-300 border border-rose-500/40 hover:bg-rose-600/30 transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Unpublish</span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('websites.publish', $website) }}"
                          onsubmit="return confirm('Publish this website live to /sites/{{ $website->slug }}?');"
                          class="inline">
                        @csrf
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white transition-all shadow-md flex items-center gap-1.5"
                                title="Publish website live to the public">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Publish Live</span>
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <!-- Main Split Workspace: Left Panel + Center Live Canvas -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Left Editing Sidebar -->
            <aside class="w-full sm:w-96 lg:w-[440px] bg-slate-950 border-r border-slate-800 flex flex-col flex-shrink-0 z-20">
                <!-- Sidebar Tabs: Sections / Theme Design / Images -->
                <div class="flex border-b border-slate-800 bg-slate-900 text-xs font-bold text-slate-400">
                    <button type="button" @click="activeTab = 'sections'"
                            class="flex-1 py-3 text-center border-b-2 transition-all flex items-center justify-center gap-1.5"
                            :class="activeTab === 'sections' ? 'border-blue-500 text-white bg-slate-950' : 'border-transparent hover:text-slate-200'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <span>Sections ({{ $sections->count() }})</span>
                    </button>

                    <button type="button" @click="activeTab = 'design'"
                            class="flex-1 py-3 text-center border-b-2 transition-all flex items-center justify-center gap-1.5"
                            :class="activeTab === 'design' ? 'border-blue-500 text-white bg-slate-950' : 'border-transparent hover:text-slate-200'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        <span>Design & Style</span>
                    </button>

                    <button type="button" @click="activeTab = 'images'"
                            class="flex-1 py-3 text-center border-b-2 transition-all flex items-center justify-center gap-1.5"
                            :class="activeTab === 'images' ? 'border-blue-500 text-white bg-slate-950' : 'border-transparent hover:text-slate-200'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Images ({{ $uploadedImages->count() }})</span>
                    </button>

                    <button type="button" @click="activeTab = 'publish'"
                            class="flex-1 py-3 text-center border-b-2 transition-all flex items-center justify-center gap-1.5"
                            :class="activeTab === 'publish' ? 'border-blue-500 text-white bg-slate-950' : 'border-transparent hover:text-slate-200'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        <span>Publish & SEO</span>
                    </button>
                </div>

                <!-- Tab 1: Section List and Editing -->
                <div x-show="activeTab === 'sections'" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Reorder & Edit Sections</span>
                        <button type="button" @click="addSectionModalOpen = true"
                                class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors flex items-center gap-1 shadow-xs">
                            <span>+ Add Section</span>
                        </button>
                    </div>

                    <!-- Sections Accordion Cards -->
                    <div class="space-y-3">
                        @foreach ($sections as $index => $sec)
                            <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden transition-all"
                                 :class="selectedSectionId === {{ $sec->id }} ? 'ring-2 ring-blue-500/50 border-blue-500' : ''">

                                <!-- Section Card Header -->
                                <div class="p-3.5 flex items-center justify-between gap-2 bg-slate-900/90 cursor-pointer"
                                     @click="selectedSectionId = (selectedSectionId === {{ $sec->id }}) ? null : {{ $sec->id }}">

                                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                        <!-- Reorder Arrows -->
                                        <div class="flex flex-col gap-0.5" @click.stop>
                                            <form method="POST" action="{{ route('websites.sections.reorder', $website) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="section_id" value="{{ $sec->id }}">
                                                <input type="hidden" name="direction" value="up">
                                                <button type="submit" class="p-0.5 text-slate-500 hover:text-white rounded" title="Move Up">
                                                    ▲
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('websites.sections.reorder', $website) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="section_id" value="{{ $sec->id }}">
                                                <input type="hidden" name="direction" value="down">
                                                <button type="submit" class="p-0.5 text-slate-500 hover:text-white rounded" title="Move Down">
                                                    ▼
                                                </button>
                                            </form>
                                        </div>

                                        <div class="truncate">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-white capitalize">{{ $allowedSections[$sec->section_type] ?? $sec->section_type }}</span>
                                                @if (!$sec->is_visible)
                                                    <span class="text-[9px] font-bold bg-amber-950 text-amber-400 px-1.5 py-0.2 rounded border border-amber-900">Hidden</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-slate-400 truncate block">
                                                {{ $sec->content['heading'] ?? $sec->content['title'] ?? 'Section Details' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Quick Actions: Hide/Show, AI Regen, Delete -->
                                    <div class="flex items-center gap-1" @click.stop>
                                        <!-- Hide/Show Form -->
                                        <form method="POST" action="{{ route('sections.toggle-visibility', $sec) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
                                                    title="{{ $sec->is_visible ? 'Hide Section' : 'Show Section' }}">
                                                @if ($sec->is_visible)
                                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>

                                        <!-- AI Regenerate Button -->
                                        <button type="button" @click="openRegenModal({{ $sec->id }}, '{{ addslashes($allowedSections[$sec->section_type] ?? $sec->section_type) }}')"
                                                class="p-1.5 text-purple-400 hover:text-purple-300 hover:bg-purple-950/50 rounded-lg transition-colors"
                                                title="Regenerate Section with Gemini">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </button>

                                        <!-- Delete Section -->
                                        <form method="POST" action="{{ route('sections.delete', $sec) }}"
                                              onsubmit="return confirm('Delete this section? You can undo or restore previous snapshots.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-rose-950/40 rounded-lg transition-colors"
                                                    title="Remove Section">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Expanded Content Editor Form -->
                                <div x-show="selectedSectionId === {{ $sec->id }}"
                                     x-collapse
                                     class="p-4 border-t border-slate-800 bg-slate-950/60 space-y-3.5 text-xs">

                                    <form method="POST" action="{{ route('sections.update', $sec) }}">
                                        @csrf
                                        @method('PUT')

                                        <!-- Common Fields: Badge, Heading, Subheading -->
                                        @if (isset($sec->content['badge']))
                                            <div>
                                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Section Badge / Tag</label>
                                                <input type="text" name="content[badge]" value="{{ $sec->content['badge'] }}"
                                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                            </div>
                                        @endif

                                        @if (isset($sec->content['heading']))
                                            <div>
                                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Heading Title</label>
                                                <input type="text" name="content[heading]" value="{{ $sec->content['heading'] }}"
                                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-bold">
                                            </div>
                                        @endif

                                        @if (isset($sec->content['subheading']))
                                            <div>
                                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Subheading</label>
                                                <textarea name="content[subheading]" rows="2"
                                                          class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">{{ $sec->content['subheading'] }}</textarea>
                                            </div>
                                        @endif

                                        <!-- Hero specific fields -->
                                        @if ($sec->section_type === 'hero')
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Primary Button</label>
                                                    <input type="text" name="content[primary_button_text]" value="{{ $sec->content['primary_button_text'] ?? '' }}"
                                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Button Link</label>
                                                    <input type="text" name="content[primary_button_link]" value="{{ $sec->content['primary_button_link'] ?? '#contact' }}"
                                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Hero Image URL</label>
                                                <div class="flex gap-2">
                                                    <input type="text" name="content[hero_image]" id="hero-img-{{ $sec->id }}" value="{{ $sec->content['hero_image'] ?? '' }}"
                                                           class="flex-1 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                                                    <button type="button" @click="activeImageField = document.getElementById('hero-img-{{ $sec->id }}'); imageModalOpen = true;"
                                                            class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold">
                                                        Browse
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- About specific fields -->
                                        @if ($sec->section_type === 'about')
                                            <div>
                                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Description Paragraph 1</label>
                                                <textarea name="content[description_p1]" rows="3"
                                                          class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs leading-relaxed">{{ $sec->content['description_p1'] ?? '' }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Description Paragraph 2</label>
                                                <textarea name="content[description_p2]" rows="2"
                                                          class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs leading-relaxed">{{ $sec->content['description_p2'] ?? '' }}</textarea>
                                            </div>
                                        @endif

                                        <!-- Contact / Location specific fields -->
                                        @if (in_array($sec->section_type, ['contact', 'location', 'footer']))
                                            @if (isset($sec->content['phone']))
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Phone Number (Confirmed)</label>
                                                    <input type="text" name="content[phone]" value="{{ $sec->content['phone'] }}"
                                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                                </div>
                                            @endif
                                            @if (isset($sec->content['email']))
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Email Address (Confirmed)</label>
                                                    <input type="email" name="content[email]" value="{{ $sec->content['email'] }}"
                                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                                </div>
                                            @endif
                                            @if (isset($sec->content['address']))
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Physical Address</label>
                                                    <input type="text" name="content[address]" value="{{ $sec->content['address'] }}"
                                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                                </div>
                                            @endif
                                            @if (isset($sec->content['operating_hours']))
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Operating Hours</label>
                                                    <input type="text" name="content[operating_hours]" value="{{ is_array($sec->content['operating_hours']) ? ($sec->content['operating_hours']['raw'] ?? '') : $sec->content['operating_hours'] }}"
                                                           class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                                </div>
                                            @endif
                                        @endif

                                        <!-- Testimonials Specific Fields -->
                                        @if ($sec->section_type === 'testimonials' && !empty($sec->content['items']))
                                            <div class="space-y-3 pt-2">
                                                <label class="block text-[11px] font-semibold text-slate-400">Client Reviews</label>
                                                @foreach ($sec->content['items'] as $tIdx => $item)
                                                    <div class="p-3 bg-slate-900 rounded-lg border border-slate-800 space-y-2">
                                                        <textarea name="content[items][{{ $tIdx }}][quote]" rows="2"
                                                                  placeholder="Client review quote"
                                                                  class="w-full px-2.5 py-1.5 rounded bg-slate-950 border border-slate-700 text-white text-xs">{{ $item['quote'] ?? '' }}</textarea>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <input type="text" name="content[items][{{ $tIdx }}][author]" value="{{ $item['author'] ?? '' }}"
                                                                   placeholder="Author name" class="px-2 py-1 rounded bg-slate-950 border border-slate-700 text-white text-xs">
                                                            <input type="text" name="content[items][{{ $tIdx }}][role]" value="{{ $item['role'] ?? '' }}"
                                                                   placeholder="Role / City" class="px-2 py-1 rounded bg-slate-950 border border-slate-700 text-white text-xs">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Save Section Changes Button -->
                                        <div class="pt-2 flex items-center justify-between">
                                            <span class="text-[10px] text-slate-500 font-mono">
                                                Type: {{ $sec->section_type }}
                                            </span>
                                            <button type="submit"
                                                    class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors">
                                                Save Section
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tab 2: Design & Theme Customization -->
                <div x-show="activeTab === 'design'" style="display: none;" class="flex-1 overflow-y-auto p-4 space-y-6 text-xs">
                    <form method="POST" action="{{ route('websites.design.update', $website) }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-white mb-2 uppercase tracking-wider">Website Title</label>
                            <input type="text" name="title" value="{{ $website->title }}"
                                   class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                        </div>

                        <!-- Aesthetic Preset Selection -->
                        <div>
                            <label class="block text-xs font-bold text-white mb-2 uppercase tracking-wider">Design Style Preset</label>
                            <div class="space-y-2">
                                @foreach ($presets as $pKey => $p)
                                    <label class="p-3 rounded-xl border flex items-center justify-between cursor-pointer transition-colors"
                                           :class="'{{ $website->design_preset }}' === '{{ $pKey }}' ? 'border-blue-500 bg-blue-950/40 text-white' : 'border-slate-800 bg-slate-900 text-slate-300 hover:border-slate-700'">
                                        <div>
                                            <input type="radio" name="design_preset" value="{{ $pKey }}"
                                                   {{ $website->design_preset === $pKey ? 'checked' : '' }} class="sr-only">
                                            <span class="font-bold text-xs block">{{ $p['name'] }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ $p['style'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $p['primary'] }};"></span>
                                            <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $p['secondary'] }};"></span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Color Customization -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 mb-1">Primary Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="primary_color" value="{{ $website->primary_color }}"
                                           class="w-8 h-8 rounded-lg border border-slate-700 bg-transparent cursor-pointer p-0.5">
                                    <input type="text" value="{{ $website->primary_color }}" readonly
                                           class="w-20 px-2 py-1 bg-slate-900 border border-slate-700 rounded text-slate-300 text-[11px] font-mono uppercase">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-300 mb-1">Secondary Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="secondary_color" value="{{ $website->secondary_color }}"
                                           class="w-8 h-8 rounded-lg border border-slate-700 bg-transparent cursor-pointer p-0.5">
                                    <input type="text" value="{{ $website->secondary_color }}" readonly
                                           class="w-20 px-2 py-1 bg-slate-900 border border-slate-700 rounded text-slate-300 text-[11px] font-mono uppercase">
                                </div>
                            </div>
                        </div>

                        <!-- Typography Font Selection -->
                        <div>
                            <label class="block text-xs font-bold text-white mb-2 uppercase tracking-wider">Typography Font</label>
                            <select name="font_family" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                                @foreach ($fonts as $fontVal => $fontLabel)
                                    <option value="{{ $fontVal }}" {{ $website->font_family === $fontVal ? 'selected' : '' }}>
                                        {{ $fontLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Button Style Variant -->
                        <div>
                            <label class="block text-xs font-bold text-white mb-2 uppercase tracking-wider">Button Style</label>
                            <select name="button_style" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                                <option value="rounded" {{ ($website->settings['button_style'] ?? '') === 'rounded' ? 'selected' : '' }}>Soft Rounded (Standard)</option>
                                <option value="pill" {{ ($website->settings['button_style'] ?? '') === 'pill' ? 'selected' : '' }}>Full Pill (Modern)</option>
                                <option value="sharp" {{ ($website->settings['button_style'] ?? '') === 'sharp' ? 'selected' : '' }}>Sharp Corners (Classic)</option>
                            </select>
                        </div>

                        <!-- Section Spacing Variant -->
                        <div>
                            <label class="block text-xs font-bold text-white mb-2 uppercase tracking-wider">Section Spacing</label>
                            <select name="section_spacing" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                                <option value="normal" {{ ($website->settings['section_spacing'] ?? '') === 'normal' ? 'selected' : '' }}>Balanced / Normal (Standard)</option>
                                <option value="compact" {{ ($website->settings['section_spacing'] ?? '') === 'compact' ? 'selected' : '' }}>Compact (Dense)</option>
                                <option value="spacious" {{ ($website->settings['section_spacing'] ?? '') === 'spacious' ? 'selected' : '' }}>Spacious (Airy & Luxurious)</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition-colors">
                            Apply Design & Themes
                        </button>
                    </form>
                </div>

                <!-- Tab 3: Uploaded Business Image Assets -->
                <div x-show="activeTab === 'images'" style="display: none;" class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Business Image Assets</span>
                        <a href="{{ route('businesses.sources.index', $business) }}" class="text-blue-400 hover:underline text-[11px]">
                            + Upload More
                        </a>
                    </div>

                    @if ($uploadedImages->isEmpty())
                        <div class="p-8 text-center text-slate-500 bg-slate-900 rounded-xl border border-slate-800">
                            <p>No image files uploaded yet.</p>
                            <a href="{{ route('businesses.sources.index', $business) }}" class="text-blue-400 underline mt-2 block">
                                Upload documents & photos
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($uploadedImages as $img)
                                <div class="bg-slate-900 p-2 rounded-xl border border-slate-800 space-y-1.5 group">
                                    <div class="aspect-4/3 rounded-lg overflow-hidden bg-black/40">
                                        <img src="{{ asset('storage/' . $img->file_path) }}" alt="{{ $img->original_name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="text-[11px] font-bold text-white truncate">{{ $img->original_name }}</div>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ asset('storage/' . $img->file_path) }}'); alert('Image URL copied to clipboard!');"
                                            class="w-full py-1 text-[10px] font-semibold rounded bg-slate-800 text-slate-300 hover:text-white transition-colors">
                                        Copy URL
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tab 4: Public Website Publishing & SEO (Phase 7) -->
                <div x-show="activeTab === 'publish'" style="display: none;" class="flex-1 overflow-y-auto p-4 space-y-5 text-xs">
                    <!-- 1. Publication Status Card -->
                    <div class="p-4 rounded-xl border {{ $website->is_published ? 'bg-emerald-950/40 border-emerald-800/60' : 'bg-slate-900 border-slate-800' }} space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Publication Status</span>
                            @if ($website->is_published)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Live to the Public
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                    Private Draft
                                </span>
                            @endif
                        </div>

                        @if ($website->is_published)
                            <div class="space-y-1">
                                <span class="text-[11px] text-slate-400">Live Public URL:</span>
                                <div class="p-2 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-between gap-2">
                                    <a href="{{ url('/sites/' . $website->slug) }}" target="_blank"
                                       class="font-mono text-emerald-400 hover:text-emerald-300 truncate text-[11px] hover:underline">
                                        {{ url('/sites/' . $website->slug) }}
                                    </a>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText('{{ url('/sites/' . $website->slug) }}'); alert('Public URL copied to clipboard!');"
                                            class="px-2 py-1 rounded bg-slate-800 text-slate-300 hover:text-white text-[10px] flex-shrink-0 transition-colors">
                                        Copy
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-500 pt-1">
                                    Published: {{ $website->published_at ? $website->published_at->format('M d, Y • H:i') : 'Recently' }}
                                </p>
                            </div>

                            <form method="POST" action="{{ route('websites.unpublish', $website) }}"
                                  onsubmit="return confirm('Take website offline? Visitors will receive a 404 until republished.');">
                                @csrf
                                <button type="submit"
                                        class="w-full py-2 rounded-xl text-xs font-semibold bg-rose-600/20 text-rose-300 border border-rose-500/40 hover:bg-rose-600/30 transition-colors">
                                    Unpublish Website (Take Offline)
                                </button>
                            </form>
                        @else
                            <p class="text-slate-400 text-[11px]">
                                Your website is currently private. Anonymous visitors will receive a 404 Not Found error until you publish.
                            </p>

                            @if ($publishingValidation['is_valid'])
                                <form method="POST" action="{{ route('websites.publish', $website) }}"
                                      onsubmit="return confirm('Publish this website live to /sites/{{ $website->slug }}?');">
                                    @csrf
                                    <button type="submit"
                                            class="w-full py-2.5 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white shadow-md transition-colors flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Publish Website Live</span>
                                    </button>
                                </form>
                            @else
                                <div class="p-2.5 rounded-lg bg-rose-950/40 border border-rose-800/60 text-rose-300 text-[11px] space-y-1">
                                    <div class="font-bold flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Publishing Blocked
                                    </div>
                                    <ul class="list-disc list-inside space-y-0.5 text-[10px]">
                                        @foreach ($publishingValidation['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- 2. Pre-Flight Validation Checklist -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Pre-Flight Quality Checklist</span>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">Business Name:</span>
                                @if (!empty(trim($business->name ?? '')))
                                    <span class="text-emerald-400 font-semibold flex items-center gap-1">✓ {{ $business->name }}</span>
                                @else
                                    <span class="text-rose-400 font-semibold">✗ Missing</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">Visible Sections:</span>
                                @php $visCount = $sections->where('is_visible', true)->count(); @endphp
                                @if ($visCount > 0)
                                    <span class="text-emerald-400 font-semibold flex items-center gap-1">✓ {{ $visCount }} sections</span>
                                @else
                                    <span class="text-rose-400 font-semibold">✗ 0 visible</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">Fact Conflicts:</span>
                                @if ($publishingValidation['is_valid'])
                                    <span class="text-emerald-400 font-semibold flex items-center gap-1">✓ No conflicts</span>
                                @else
                                    <a href="{{ route('businesses.facts.index', $business) }}" class="text-rose-400 hover:underline font-semibold flex items-center gap-1">
                                        Review Facts →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 3. Custom Vanity URL Slug Editor -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Vanity URL Slug</span>
                            <p class="text-[11px] text-slate-400 mt-0.5">Customize the public web address for your website.</p>
                        </div>

                        <form method="POST" action="{{ route('websites.slug.update', $website) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Public URL Slug</label>
                                <div class="flex items-center rounded-lg border border-slate-800 bg-slate-950 overflow-hidden text-xs">
                                    <span class="px-2.5 py-1.5 text-slate-500 font-mono select-none bg-slate-900 border-r border-slate-800">
                                        /sites/
                                    </span>
                                    <input type="text" name="slug" value="{{ old('slug', $website->slug) }}" required minlength="3" maxlength="80"
                                           class="flex-1 bg-transparent px-2.5 py-1.5 text-white font-mono focus:outline-hidden focus:ring-1 focus:ring-blue-500">
                                </div>
                                <span class="text-[10px] text-slate-500 mt-1 block">3–80 characters, lowercase letters, numbers, and hyphens.</span>
                            </div>

                            <button type="submit"
                                    class="w-full py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                                Update URL Slug
                            </button>
                        </form>
                    </div>

                    <!-- 4. SEO & Social Sharing Metadata -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">SEO & Social Sharing</span>
                            <p class="text-[11px] text-slate-400 mt-0.5">Control how your website appears on Google, WhatsApp, Facebook, and Twitter.</p>
                        </div>

                        <form method="POST" action="{{ route('websites.seo.update', $website) }}" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Custom SEO Title</label>
                                <input type="text" name="seo_title"
                                       value="{{ old('seo_title', $website->settings['seo_title'] ?? '') }}"
                                       placeholder="{{ $website->title }} — {{ $business->city ?? 'Official Website' }}"
                                       maxlength="120"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-800 bg-slate-950 text-white focus:outline-hidden focus:ring-1 focus:ring-blue-500">
                                <span class="text-[10px] text-slate-500 mt-0.5 block">Recommended length: 50–60 characters.</span>
                            </div>

                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Meta Description <span class="text-rose-400">*</span></label>
                                <textarea name="meta_description" rows="3" required maxlength="200"
                                          placeholder="A compelling, fact-accurate summary of your business..."
                                          class="w-full px-2.5 py-1.5 rounded-lg border border-slate-800 bg-slate-950 text-white focus:outline-hidden focus:ring-1 focus:ring-blue-500">{{ old('meta_description', $website->meta_description) }}</textarea>
                                <span class="text-[10px] text-slate-500 mt-0.5 block">Recommended length: 140–160 characters (Max 200).</span>
                            </div>

                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Open Graph Social Image URL</label>
                                <input type="url" name="og_image"
                                       value="{{ old('og_image', $website->settings['og_image'] ?? '') }}"
                                       placeholder="https://example.com/cover.jpg"
                                       maxlength="255"
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-slate-800 bg-slate-950 text-white focus:outline-hidden focus:ring-1 focus:ring-blue-500">
                                <span class="text-[10px] text-slate-500 mt-0.5 block">Preview image displayed when link is shared on social media.</span>
                            </div>

                            <button type="submit"
                                    class="w-full py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                                Save SEO & Social Metadata
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Center Live Interactive Preview Canvas -->
            <main class="flex-1 flex items-center justify-center p-2 sm:p-6 overflow-y-auto bg-slate-950/40">
                <div :class="getContainerClass()" class="overflow-hidden bg-white">
                    <iframe id="preview-iframe"
                            src="{{ route('websites.preview.frame', $website) }}"
                            title="Live Website Preview"
                            class="w-full h-full border-0"></iframe>
                </div>
            </main>
        </div>

        <!-- 1. ADD SECTION MODAL -->
        <div x-show="addSectionModalOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="addSectionModalOpen" x-transition.opacity
                     class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity"
                     @click="addSectionModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="addSectionModalOpen" x-transition.scale
                     class="inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-800 p-6">
                    <h3 class="text-base font-bold text-white mb-2">Add New Section</h3>
                    <p class="text-xs text-slate-400 mb-4">Choose a supported section to append to your website.</p>

                    <form method="POST" action="{{ route('websites.sections.add', $website) }}">
                        @csrf
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @foreach ($allowedSections as $secKey => $secTitle)
                                <label class="p-3 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 flex items-center justify-between cursor-pointer text-xs font-semibold text-slate-300">
                                    <span>{{ $secTitle }}</span>
                                    <input type="radio" name="section_type" value="{{ $secKey }}"
                                           {{ $secKey === 'about' ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-700 bg-slate-900">
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="addSectionModalOpen = false"
                                    class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition-colors">
                                Add Section
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. GEMINI AI SECTION REGENERATION APPROVAL MODAL (SIDE-BY-SIDE DIFF) -->
        <div x-show="regenModalOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="regenModalOpen" x-transition.opacity
                     class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity"
                     @click="regenModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="regenModalOpen" x-transition.scale
                     class="inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-800 p-6 sm:p-8">

                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold">
                                ✦
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">Regenerate Section: <span x-text="regenSectionTitle"></span></h3>
                                <p class="text-xs text-slate-400">Powered by Gemini AI • Requires Owner Review & Approval</p>
                            </div>
                        </div>
                        <button type="button" @click="regenModalOpen = false" class="text-slate-500 hover:text-white">✕</button>
                    </div>

                    <!-- Prompt Input Area -->
                    <div class="mt-4 space-y-3">
                        <label class="block text-xs font-bold text-slate-300">
                            Custom Creative Direction (Optional)
                        </label>
                        <div class="flex gap-2">
                            <input type="text" x-model="regenUserPrompt"
                                   placeholder="e.g. Make it more professional and concise, emphasize our organic ingredients..."
                                   class="flex-1 px-3.5 py-2 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white focus:ring-2 focus:ring-purple-500">
                            <button type="button" @click="requestRegenPreview()"
                                    :disabled="regenerating"
                                    class="px-5 py-2 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-700 text-white shadow-xs transition-colors flex items-center gap-2"
                                    :class="regenerating ? 'opacity-60 cursor-not-allowed' : ''">
                                <span x-show="!regenerating">Generate Preview</span>
                                <span x-show="regenerating">Generating...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Side-by-Side Comparison (Original vs Proposed) -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Original Content -->
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Current Active Version</span>
                                <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded">Untouched</span>
                            </div>
                            <pre class="text-[11px] font-mono text-slate-300 overflow-x-auto p-2 bg-slate-900 rounded max-h-60"
                                 x-text="JSON.stringify(regenOriginal, null, 2)"></pre>
                        </div>

                        <!-- Proposed AI Content -->
                        <div class="p-4 rounded-xl bg-purple-950/20 border border-purple-500/40 space-y-2">
                            <div class="flex items-center justify-between border-b border-purple-500/30 pb-2">
                                <span class="text-xs font-bold text-purple-300 uppercase tracking-wider">AI Proposed Version</span>
                                <span class="text-[10px] bg-purple-900 text-purple-200 px-2 py-0.5 rounded">Pending Approval</span>
                            </div>
                            <template x-if="regenProposed">
                                <pre class="text-[11px] font-mono text-purple-200 overflow-x-auto p-2 bg-slate-900 rounded max-h-60"
                                     x-text="JSON.stringify(regenProposed, null, 2)"></pre>
                            </template>
                            <template x-if="!regenProposed">
                                <div class="py-12 text-center text-xs text-slate-500">
                                    Click "Generate Preview" to formulate a grounded proposal.
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Decision Action Buttons -->
                    <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="regenModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white transition-colors">
                            Reject & Keep Original
                        </button>

                        <button type="button" @click="acceptRegen()"
                                :disabled="!regenProposed"
                                class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors"
                                :class="!regenProposed ? 'opacity-50 cursor-not-allowed' : ''">
                            Accept & Apply to Section
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. IMAGE SELECTOR MODAL -->
        <div x-show="imageModalOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="imageModalOpen" x-transition.opacity
                     class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity"
                     @click="imageModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="imageModalOpen" x-transition.scale
                     class="inline-block align-bottom bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-800 p-6">
                    <h3 class="text-base font-bold text-white mb-2">Select Image Asset</h3>
                    <p class="text-xs text-slate-400 mb-4">Click an image uploaded for this business to assign it to this section.</p>

                    @if ($uploadedImages->isEmpty())
                        <div class="py-8 text-center text-xs text-slate-500 bg-slate-950 rounded-xl border border-slate-800">
                            No uploaded images available. Please upload files in Document Management first.
                        </div>
                    @else
                        <div class="grid grid-cols-3 gap-3 max-h-72 overflow-y-auto pr-1">
                            @foreach ($uploadedImages as $img)
                                <div @click="selectImage('{{ asset('storage/' . $img->file_path) }}')"
                                     class="p-2 rounded-xl border border-slate-800 bg-slate-950 hover:border-blue-500 cursor-pointer group transition-all">
                                    <div class="aspect-4/3 rounded-lg overflow-hidden bg-black/40">
                                        <img src="{{ asset('storage/' . $img->file_path) }}" alt="{{ $img->original_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                    </div>
                                    <span class="text-[10px] text-slate-400 truncate block mt-1">{{ $img->original_name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="imageModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
