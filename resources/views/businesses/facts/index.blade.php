<x-app-layout :title="'Fact Verification — ' . $business->name">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        x-data="{
            editModalOpen: false,
            editFactName: '',
            editFactValue: '',
            editActionUrl: '',
            rejectModalOpen: false,
            rejectFactName: '',
            rejectActionUrl: '',
            manualModalOpen: false,
            manualFactKey: 'phone',
            manualFactName: 'Phone Number',
            manualFactValue: '',
            evidenceModalOpen: false,
            evidenceTitle: '',
            evidenceText: '',
            evidenceSource: '',
            evidenceDate: ''
        }">

        <!-- Top Breadcrumbs & Header -->
        <div class="mb-6">
            <nav class="flex items-center text-xs font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('businesses.show', $business) }}" class="hover:text-slate-800">{{ $business->name }}</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-semibold">Fact Verification</span>
            </nav>

            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Fact Extraction & Verification</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            Phase 4
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        <strong>Golden Rule: Never Invent Facts.</strong> Confirm extracted information, resolve source conflicts, and supply any missing business details.
                    </p>
                </div>

                <div class="mt-4 sm:mt-0 flex items-center gap-2.5">
                    <button type="button" @click="manualModalOpen = true"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                        + Add Fact Manually
                    </button>

                    <form method="POST" action="{{ route('businesses.facts.reextract', $business) }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-2xs transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Re-extract</span>
                        </button>
                    </form>

                    <a href="{{ route('businesses.website.setup', $business) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Generate Website →</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Verification Health & Readiness Banner -->
        <div class="mb-6 p-5 rounded-2xl border shadow-xs
            {{ $analysis['has_blocking_conflicts'] ? 'bg-amber-50/80 border-amber-200 text-amber-900' : 'bg-emerald-50/80 border-emerald-200 text-emerald-900' }}">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5
                        {{ $analysis['has_blocking_conflicts'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        @if ($analysis['has_blocking_conflicts'])
                            !
                        @else
                            ✓
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">
                            @if ($analysis['has_blocking_conflicts'])
                                Blocking Information Conflicts Detected ({{ count($analysis['conflicts']) }})
                            @else
                                Facts Verified & Validated
                            @endif
                        </h3>
                        <p class="text-xs mt-0.5 leading-relaxed opacity-90">
                            @if ($analysis['has_blocking_conflicts'])
                                Multiple sources report contradictory details for the same attribute (e.g. phone numbers or addresses). The system will not generate website sections until you confirm the correct truth.
                            @else
                                Factual grounding complete. Confirmed business facts are ready to feed into the AI website generation engine.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="text-right flex-shrink-0 hidden sm:block">
                    <span class="text-xs font-semibold uppercase tracking-wider opacity-75">Status</span>
                    <div class="text-sm font-bold mt-0.5">
                        {{ count($analysis['confirmed']) }} Confirmed • {{ count($analysis['conflicts']) }} Conflicting
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-4 mb-6 text-xs font-semibold">
            <a href="{{ route('businesses.facts.index', ['business' => $business, 'filter' => 'all']) }}"
                class="px-3.5 py-1.5 rounded-xl transition-all {{ $activeFilter === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                All Facts ({{ $analysis['all_facts']->count() }})
            </a>

            <a href="{{ route('businesses.facts.index', ['business' => $business, 'filter' => 'conflicts']) }}"
                class="px-3.5 py-1.5 rounded-xl transition-all {{ $activeFilter === 'conflicts' ? 'bg-amber-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                Conflicting ({{ count($analysis['conflicts']) }})
            </a>

            <a href="{{ route('businesses.facts.index', ['business' => $business, 'filter' => 'confirmed']) }}"
                class="px-3.5 py-1.5 rounded-xl transition-all {{ $activeFilter === 'confirmed' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                Confirmed ({{ count($analysis['confirmed']) }})
            </a>

            <a href="{{ route('businesses.facts.index', ['business' => $business, 'filter' => 'unverified']) }}"
                class="px-3.5 py-1.5 rounded-xl transition-all {{ $activeFilter === 'unverified' ? 'bg-blue-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                Unverified ({{ count($analysis['unverified']) }})
            </a>

            <a href="{{ route('businesses.facts.index', ['business' => $business, 'filter' => 'missing']) }}"
                class="px-3.5 py-1.5 rounded-xl transition-all {{ $activeFilter === 'missing' ? 'bg-rose-600 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                Missing Fields ({{ count($analysis['missing']) }})
            </a>

            <a href="{{ route('businesses.facts.index', ['business' => $business, 'filter' => 'rejected']) }}"
                class="px-3.5 py-1.5 rounded-xl transition-all {{ $activeFilter === 'rejected' ? 'bg-slate-700 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-100' }}">
                Rejected & Outdated ({{ count($analysis['rejected']) + count($analysis['outdated']) }})
            </a>
        </div>

        <!-- 1. Conflicting Facts Section (Top Priority Resolution) -->
        @if (count($analysis['conflicts']) > 0 && in_array($activeFilter, ['all', 'conflicts']))
            <div class="mb-10 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                    <h2 class="text-base font-bold text-slate-900">Conflicting Information Requiring Resolution</h2>
                </div>

                @foreach ($analysis['conflicts'] as $key => $conflict)
                    <div class="bg-white p-6 rounded-2xl border-2 border-amber-300 shadow-xs space-y-4">
                        <div class="sm:flex sm:items-center sm:justify-between border-b border-slate-100 pb-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                    Conflict Detected
                                </span>
                                <h3 class="text-base font-bold text-slate-900 mt-1">{{ $conflict['fact_name'] }}</h3>
                            </div>
                            <span class="text-xs text-slate-500 mt-1 sm:mt-0 font-medium">
                                {{ count($conflict['candidates']) }} conflicting candidates found
                            </span>
                        </div>

                        <p class="text-xs text-slate-600">
                            Different sources report conflicting information for <strong>{{ $conflict['fact_name'] }}</strong>. Please select the correct value below, or supply a custom replacement:
                        </p>

                        <!-- Conflict Candidate Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($conflict['candidates'] as $candidate)
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 hover:border-blue-400 transition-all flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                            <span class="font-semibold uppercase tracking-wider text-[10px]">
                                                Source: {{ ucfirst($candidate['source_type']) }}
                                            </span>
                                            @if ($candidate['confidence'])
                                                <span>{{ round($candidate['confidence'] * 100) }}% confidence</span>
                                            @endif
                                        </div>

                                        <div class="text-sm font-bold text-slate-900 break-all">
                                            {{ $candidate['value'] }}
                                        </div>

                                        @if ($candidate['evidence'])
                                            <div class="mt-2 text-[11px] text-slate-500 bg-white p-2.5 rounded-lg border border-slate-200/80 italic">
                                                "{{ Str::limit($candidate['evidence'], 120) }}"
                                            </div>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('businesses.facts.resolve-conflict', $business) }}" class="mt-4 pt-2 border-t border-slate-200/80">
                                        @csrf
                                        <input type="hidden" name="fact_key" value="{{ $conflict['fact_key'] }}">
                                        <input type="hidden" name="confirmed_value" value="{{ $candidate['value'] }}">
                                        <button type="submit"
                                            class="w-full py-1.5 px-3 rounded-lg text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-2xs">
                                            Confirm This Value as Correct
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>

                        <!-- Custom Resolution Form -->
                        <div class="pt-2 border-t border-slate-100" x-data="{ customOpen: false }">
                            <button type="button" @click="customOpen = !customOpen" class="text-xs font-semibold text-blue-600 hover:underline">
                                None of these are right? Enter custom correct value →
                            </button>

                            <div x-show="customOpen" style="display: none;" class="mt-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <form method="POST" action="{{ route('businesses.facts.resolve-conflict', $business) }}" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="fact_key" value="{{ $conflict['fact_key'] }}">
                                    <label class="block text-xs font-semibold text-slate-700">Custom Correct Value</label>
                                    <input type="text" name="confirmed_value" required
                                        placeholder="Enter the authentic value..."
                                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg shadow-2xs focus:ring-2 focus:ring-blue-500 bg-white">
                                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-2xs">
                                        Save Custom Truth
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- 2. Missing Critical Information Section -->
        @if (count($analysis['missing']) > 0 && in_array($activeFilter, ['all', 'missing']))
            <div class="mb-10 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                    <h2 class="text-base font-bold text-slate-900">Missing Core Business Details ({{ count($analysis['missing']) }})</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($analysis['missing'] as $key => $miss)
                        <div class="bg-white p-5 rounded-2xl border border-rose-200 shadow-xs flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-900">{{ $miss['fact_name'] }}</h3>
                                    <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                                        Missing
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2">{{ $miss['prompt'] }}</p>
                            </div>

                            <form method="POST" action="{{ route('businesses.facts.manual', $business) }}" class="mt-4 pt-3 border-t border-slate-100 flex gap-2">
                                @csrf
                                <input type="hidden" name="fact_key" value="{{ $miss['fact_key'] }}">
                                <input type="hidden" name="fact_name" value="{{ $miss['fact_name'] }}">
                                <input type="text" name="fact_value" required
                                    placeholder="Enter {{ strtolower($miss['fact_name']) }}..."
                                    class="flex-grow px-3 py-1.5 text-xs border border-slate-300 rounded-lg shadow-2xs focus:ring-2 focus:ring-blue-500">
                                <button type="submit"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-900 text-white hover:bg-slate-800 shadow-2xs flex-shrink-0">
                                    Save
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 3. Primary Extracted Facts Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                    Extracted Business Facts ({{ $analysis['all_facts']->count() }})
                </h3>
                <span class="text-xs text-slate-500">Traceable to original evidence</span>
            </div>

            @if ($analysis['all_facts']->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-900">No facts extracted yet</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Upload a PDF, menu, or crawl your website in Phase 3, or add business details manually using the button above.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-6 py-3.5">Category</th>
                                <th scope="col" class="px-6 py-3.5">Factual Value</th>
                                <th scope="col" class="px-6 py-3.5">Source & Evidence</th>
                                <th scope="col" class="px-6 py-3.5">Status</th>
                                <th scope="col" class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($analysis['all_facts'] as $fact)
                                @php
                                    // Apply filter if selected
                                    if ($activeFilter === 'confirmed' && $fact->verification_status !== 'user-confirmed') continue;
                                    if ($activeFilter === 'conflicts' && $fact->verification_status !== 'conflicting') continue;
                                    if ($activeFilter === 'unverified' && !in_array($fact->verification_status, ['extracted', 'source-supported'])) continue;
                                    if ($activeFilter === 'rejected' && !in_array($fact->verification_status, ['rejected', 'outdated'])) continue;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <!-- Category -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900 text-xs">{{ $fact->fact_name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $fact->fact_key }}</div>
                                    </td>

                                    <!-- Value -->
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-semibold text-slate-800 max-w-xs break-all">
                                            {{ $fact->user_confirmed_value ?: $fact->fact_value }}
                                        </div>
                                        @if ($fact->is_owner_provided)
                                            <span class="inline-flex items-center text-[10px] text-slate-500 font-medium mt-1">
                                                (Owner-supplied)
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Evidence & Traceability -->
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-slate-700 max-w-xs">
                                            <div class="font-medium text-[11px] text-slate-500">
                                                {{ ucfirst($fact->source_type) }}: {{ $fact->source_url ?: 'Document File' }}
                                            </div>
                                            @if ($fact->evidence || $fact->notes)
                                                <button type="button"
                                                    @click="evidenceModalOpen = true; evidenceTitle = '{{ addslashes($fact->fact_name) }}'; evidenceText = `{{ addslashes($fact->evidence ?: $fact->notes) }}`; evidenceSource = '{{ addslashes($fact->source_url ?: $fact->source_type) }}'; evidenceDate = '{{ $fact->extraction_time ? $fact->extraction_time->format('M d, Y H:i') : '' }}'"
                                                    class="text-[11px] text-blue-600 hover:underline block mt-0.5 truncate text-left max-w-xs">
                                                    "{{ Str::limit($fact->evidence ?: $fact->notes, 60) }}"
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($fact->verification_status === 'user-confirmed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Confirmed
                                            </span>
                                        @elseif ($fact->verification_status === 'conflicting')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Conflicting
                                            </span>
                                        @elseif ($fact->verification_status === 'rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                Rejected
                                            </span>
                                        @elseif ($fact->verification_status === 'outdated')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                                                Outdated
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                Unverified
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-1">
                                        @if ($fact->verification_status !== 'user-confirmed')
                                            <form method="POST" action="{{ route('facts.confirm', $fact) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-emerald-700 bg-emerald-50 hover:bg-emerald-100 font-semibold transition-colors">
                                                    Confirm
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button"
                                            @click="editModalOpen = true; editFactName = '{{ addslashes($fact->fact_name) }}'; editFactValue = '{{ addslashes($fact->user_confirmed_value ?: $fact->fact_value) }}'; editActionUrl = '{{ route('facts.edit-confirm', $fact) }}'"
                                            class="px-2.5 py-1 rounded-lg text-slate-700 bg-slate-100 hover:bg-slate-200 font-semibold transition-colors">
                                            Edit
                                        </button>

                                        @if ($fact->verification_status !== 'rejected')
                                            <button type="button"
                                                @click="rejectModalOpen = true; rejectFactName = '{{ addslashes($fact->fact_name) }}'; rejectActionUrl = '{{ route('facts.reject', $fact) }}'"
                                                class="px-2 py-1 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors">
                                                Reject
                                            </button>
                                        @endif

                                        @if ($fact->verification_status !== 'outdated')
                                            <form method="POST" action="{{ route('facts.outdated', $fact) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100" title="Mark Outdated">
                                                    Outdated
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Alpine.js Edit & Confirm Modal -->
        <div x-show="editModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="editModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                    <form :action="editActionUrl" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <h3 class="text-base font-bold text-slate-900">Edit & Confirm Fact</h3>
                            <p class="text-xs text-slate-500">Provide the correct value for <span class="font-bold text-slate-700" x-text="editFactName"></span>.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Confirmed Value</label>
                            <textarea name="fact_value" x-model="editFactValue" required rows="3"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="editModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-xs">
                                Save & Confirm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alpine.js Reject Modal -->
        <div x-show="rejectModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="rejectModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="rejectModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="rejectModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <form :action="rejectActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Reject Fact</h3>
                            <p class="text-xs text-slate-500">Mark <span class="font-bold text-slate-700" x-text="rejectFactName"></span> as rejected. It will not be included on your website.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Reason for Rejection (Optional)</label>
                            <input type="text" name="reason"
                                placeholder="e.g. Outdated phone number from old branch"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="rejectModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-xs">
                                Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alpine.js Manual Fact Entry Modal -->
        <div x-show="manualModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="manualModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="manualModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="manualModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                    <form method="POST" action="{{ route('businesses.facts.manual', $business) }}" class="space-y-4">
                        @csrf
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Add Business Fact Manually</h3>
                            <p class="text-xs text-slate-500">Provide direct business information (will be labeled as owner-supplied).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                            <select name="fact_key" x-model="manualFactKey"
                                @change="manualFactName = $event.target.options[$event.target.selectedIndex].text"
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="phone">Phone Number</option>
                                <option value="email">Email Address</option>
                                <option value="address">Physical Address</option>
                                <option value="operating_hours">Operating Hours</option>
                                <option value="services">Services / Offerings</option>
                                <option value="products">Products List</option>
                                <option value="pricing">Pricing Information</option>
                                <option value="features">Facilities & Features</option>
                                <option value="certifications">Certifications & Accreditations</option>
                                <option value="awards">Awards & Recognitions</option>
                                <option value="other">Other Business Detail</option>
                            </select>
                        </div>

                        <input type="hidden" name="fact_name" :value="manualFactName">

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Fact Value</label>
                            <textarea name="fact_value" required rows="3"
                                placeholder="Enter value..."
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="manualModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-xs">
                                Save Fact
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alpine.js Traceable Evidence Modal -->
        <div x-show="evidenceModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="evidenceModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="evidenceModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="evidenceModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Source Evidence Trace</h3>
                            <p class="text-xs text-slate-500" x-text="evidenceTitle"></p>
                        </div>
                        <button type="button" @click="evidenceModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4 space-y-3 text-xs">
                        <div>
                            <span class="font-semibold text-slate-500">Originating Source:</span>
                            <div class="font-mono text-slate-800 break-all mt-0.5" x-text="evidenceSource"></div>
                        </div>

                        <div>
                            <span class="font-semibold text-slate-500">Supporting Verbatim Excerpt:</span>
                            <div class="mt-1 p-3.5 bg-slate-50 rounded-xl border border-slate-200 font-mono text-slate-700 leading-relaxed italic"
                                x-text="evidenceText">
                            </div>
                        </div>

                        <div x-show="evidenceDate">
                            <span class="font-semibold text-slate-500">Extracted Timestamp:</span>
                            <span class="text-slate-700 ml-1" x-text="evidenceDate"></span>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button" @click="evidenceModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
