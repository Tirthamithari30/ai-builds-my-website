<x-app-layout :title="'Generate Website — ' . $business->name">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="{
             selectedPreset: 'modern_minimal',
             primaryColor: '#2563eb',
             secondaryColor: '#0f172a',
             updatePreset(preset, primary, secondary) {
                 this.selectedPreset = preset;
                 this.primaryColor = primary;
                 this.secondaryColor = secondary;
             }
         }">

        <!-- Breadcrumbs -->
        <nav class="flex items-center text-xs font-medium text-slate-500 mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('businesses.show', $business) }}" class="hover:text-slate-800">{{ $business->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-semibold">Website Setup</span>
        </nav>

        <!-- Page Header -->
        <div class="sm:flex sm:items-center sm:justify-between pb-6 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Generate Website for {{ $business->name }}
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        Phase 5 Engine
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    Review confirmed business facts, choose your aesthetic style, and generate a complete website with Gemini AI.
                </p>
            </div>

            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                <a href="{{ route('businesses.facts.index', $business) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back to Fact Review</span>
                </a>
            </div>
        </div>

        <!-- 1. BLOCKING CONFLICT ALERT (IF UNRESOLVED CONFLICTS EXIST) -->
        @if ($hasConflicts)
            <div class="mt-6 p-6 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 shadow-xs" role="alert">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-rose-200 text-rose-700 flex items-center justify-center font-bold text-lg flex-shrink-0 mt-0.5">
                        !
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-rose-900">Unresolved Conflicting Information Detected</h2>
                        <p class="text-xs text-rose-700 mt-1 leading-relaxed">
                            Your business data contains conflicting facts (e.g. differing phone numbers, addresses, or hours across different sources).
                            Per the <strong>Fact Grounding Rule</strong>, websites cannot be generated while contradictory claims remain unresolved.
                        </p>
                        <div class="mt-4 flex items-center gap-3">
                            <a href="{{ route('businesses.facts.index', $business) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition-colors">
                                <span>Resolve Conflicts in Fact Review</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. PRE-GENERATION REVIEW (GROUNDED FACTS AUDIT) -->
        <div class="mt-8 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Pre-Generation Fact Review</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Only verified facts below will be used as the factual ground truth.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Zero Invented Facts</span>
                </div>
            </div>

            <!-- Grounded Facts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                <!-- Business Name & Category -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Business Identity</span>
                    <div class="text-sm font-bold text-slate-900 mt-1">{{ $business->name }}</div>
                    <div class="text-xs text-slate-600 mt-0.5">{{ ucwords(str_replace('_', ' ', $business->category)) }}</div>
                </div>

                <!-- Confirmed Phone -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Phone Number</span>
                        @if (!empty($verifiedDict['phone']))
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100/60 px-1.5 py-0.5 rounded">Confirmed</span>
                        @endif
                    </div>
                    <div class="text-sm font-bold text-slate-900 mt-1">
                        {{ $verifiedDict['phone'] ?? ($business->phone ?: 'Not provided (will be omitted)') }}
                    </div>
                </div>

                <!-- Confirmed Email -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Email Address</span>
                        @if (!empty($verifiedDict['email']))
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100/60 px-1.5 py-0.5 rounded">Confirmed</span>
                        @endif
                    </div>
                    <div class="text-sm font-bold text-slate-900 mt-1">
                        {{ $verifiedDict['email'] ?? ($business->email ?: 'Not provided (will be omitted)') }}
                    </div>
                </div>

                <!-- Confirmed Location -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 md:col-span-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Physical Location & Address</span>
                        @if (!empty($verifiedDict['address']))
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100/60 px-1.5 py-0.5 rounded">Confirmed</span>
                        @endif
                    </div>
                    <div class="text-sm font-semibold text-slate-900 mt-1">
                        {{ $verifiedDict['address'] ?? ($business->address ?: 'No physical address supplied') }}
                        @if (!empty($verifiedDict['city']))
                            <span class="text-slate-500 block text-xs mt-0.5">City: {{ $verifiedDict['city'] }}{{ !empty($verifiedDict['state']) ? ', ' . $verifiedDict['state'] : '' }}</span>
                        @endif
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Operating Hours</span>
                        @if (!empty($verifiedDict['operating_hours']))
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100/60 px-1.5 py-0.5 rounded">Confirmed</span>
                        @endif
                    </div>
                    <div class="text-xs font-semibold text-slate-900 mt-1">
                        @php
                            $hrs = $verifiedDict['operating_hours'] ?? $business->operating_hours;
                        @endphp
                        @if (is_array($hrs))
                            {{ $hrs['raw'] ?? 'Standard operating hours' }}
                        @elseif ($hrs)
                            {{ $hrs }}
                        @else
                            <span class="text-slate-400">Not provided</span>
                        @endif
                    </div>
                </div>

                <!-- Business Description -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 md:col-span-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Confirmed Description</span>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100/60 px-1.5 py-0.5 rounded">Verified Source</span>
                    </div>
                    <p class="text-xs text-slate-700 leading-relaxed">
                        {{ $verifiedDict['description'] ?? ($business->description ?: 'Standard business overview based on verified category standards.') }}
                    </p>
                </div>
            </div>

            <!-- Uploaded Image Assets -->
            @if ($imageFiles->isNotEmpty())
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Available Business Photos ({{ $imageFiles->count() }})</h3>
                    <div class="flex items-center gap-4 overflow-x-auto pb-2">
                        @foreach ($imageFiles as $img)
                            <div class="relative w-20 h-20 rounded-xl overflow-hidden border border-slate-200 shadow-2xs flex-shrink-0 group">
                                <img src="{{ asset('storage/' . $img->file_path) }}" alt="{{ $img->original_name }}" class="w-full h-full object-cover">
                                <span class="absolute bottom-0 inset-x-0 bg-black/60 text-white text-[9px] px-1 py-0.5 truncate text-center">{{ $img->original_name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Missing Optional Facts Notice -->
            @php
                $missingOptional = [];
                foreach (\App\Services\FactVerificationService::OPTIONAL_FACTS as $optKey => $optLabel) {
                    if (empty($verifiedDict[$optKey])) {
                        $missingOptional[] = $optLabel;
                    }
                }
                $allMissing = array_merge(array_column($factAnalysis['missing'] ?? [], 'fact_name'), $missingOptional);
            @endphp
            @if (!empty($allMissing))
                <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-xs text-slate-600 leading-relaxed">
                        <strong class="font-bold text-slate-900">Missing Information Handled Accurately:</strong>
                        The following optional details have not been provided:
                        <span class="font-semibold text-slate-700">{{ implode(', ', array_slice($allMissing, 0, 5)) }}</span>.
                        In compliance with our Golden Rule, these items will be omitted rather than invented.
                    </div>
                </div>
            @endif
        </div>

        <!-- 3. WEBSITE GENERATION OPTIONS FORM -->
        <form method="POST" action="{{ route('businesses.website.generate', $business) }}" class="mt-8 space-y-8">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-8">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Design & Layout Preferences</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Customize the visual styling, theme, and sections for your website.</p>
                </div>

                <!-- 1. Website Title -->
                <div>
                    <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Website Title <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title', $existingWebsite->title ?? $business->name) }}"
                           placeholder="Enter public website title"
                           class="w-full max-w-xl px-4 py-2.5 text-sm rounded-xl border border-slate-300 shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-slate-500 mt-1">This will appear in your website header, meta tags, and browser tab.</p>
                </div>

                <!-- 2. Website Type -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                        Select Website Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($websiteTypes as $typeKey => $t)
                            <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all hover:border-blue-300"
                                   :class="selectedType === '{{ $typeKey }}' ? 'border-blue-600 bg-blue-50/40 shadow-xs' : 'border-slate-200 bg-white'"
                                   x-data="{ selectedType: '{{ old('website_type', 'business') }}' }">
                                <input type="radio" name="website_type" value="{{ $typeKey }}"
                                       {{ old('website_type', 'business') === $typeKey ? 'checked' : '' }}
                                       @change="selectedType = '{{ $typeKey }}'"
                                       class="sr-only">
                                <span class="text-sm font-bold text-slate-900">{{ $t['name'] }}</span>
                                <span class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $t['description'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Design Presets -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                        Choose Design Aesthetic Preset <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach ($presets as $pKey => $preset)
                            <div @click="updatePreset('{{ $pKey }}', '{{ $preset['primary'] }}', '{{ $preset['secondary'] }}')"
                                 class="p-5 rounded-2xl border-2 cursor-pointer transition-all"
                                 :class="selectedPreset === '{{ $pKey }}' ? 'border-blue-600 bg-blue-50/30 shadow-md ring-2 ring-blue-500/20' : 'border-slate-200 bg-white hover:border-slate-300'">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-extrabold text-slate-900">{{ $preset['name'] }}</h3>
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded-full border border-black/10" style="background-color: {{ $preset['primary'] }};"></span>
                                        <span class="w-4 h-4 rounded-full border border-black/10" style="background-color: {{ $preset['secondary'] }};"></span>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $preset['style'] }}</p>
                                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 font-mono">
                                    Typography: {{ explode(',', $preset['font'])[0] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="design_preset" :value="selectedPreset">
                </div>

                <!-- 4. Color Palette Customization -->
                <div class="pt-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">
                        Brand Colors
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-xl">
                        <!-- Primary Color -->
                        <div>
                            <span class="block text-xs font-semibold text-slate-600 mb-1.5">Primary Accent Color</span>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="primaryColor"
                                       class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                <input type="text" name="primary_color" x-model="primaryColor" required
                                       class="w-28 px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 shadow-2xs uppercase">
                            </div>
                        </div>

                        <!-- Secondary Color -->
                        <div>
                            <span class="block text-xs font-semibold text-slate-600 mb-1.5">Secondary Header Color</span>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="secondaryColor"
                                       class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                <input type="text" name="secondary_color" x-model="secondaryColor" required
                                       class="w-28 px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 shadow-2xs uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Sections Selection -->
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Website Sections to Include <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-xs text-slate-500">Checked sections will be generated</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        @foreach ($allowedSections as $secKey => $secTitle)
                            @php
                                $isChecked = in_array($secKey, old('sections', $defaultSections));
                            @endphp
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-slate-100/60 cursor-pointer transition-colors text-xs font-semibold text-slate-800">
                                <input type="checkbox" name="sections[]" value="{{ $secKey }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                <span>{{ $secTitle }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 6. Custom Instructions (Optional) -->
                <div class="pt-2">
                    <label for="custom_instructions" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Creative Direction & Custom Instructions (Optional)
                    </label>
                    <textarea name="custom_instructions" id="custom_instructions" rows="3"
                              placeholder="e.g. Highlight our friendly local heritage, focus on lunch specials, or maintain a clean executive tone..."
                              class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('custom_instructions') }}</textarea>
                    <p class="text-xs text-slate-500 mt-1">Instructions guide copywriting style only; factual claims will always be bound to verified facts.</p>
                </div>
            </div>

            <!-- Submit Action Bar -->
            <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                <a href="{{ route('businesses.show', $business) }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-semibold border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>

                <div class="flex items-center gap-3">
                    @if ($hasConflicts)
                        <div class="text-xs text-rose-600 font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Resolve conflicts above to enable generation</span>
                        </div>
                    @endif

                    <button type="submit"
                            {{ $hasConflicts ? 'disabled' : '' }}
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white shadow-xs transition-all {{ $hasConflicts ? 'bg-slate-300 cursor-not-allowed opacity-60' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/25' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>Generate Website with Gemini AI</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
