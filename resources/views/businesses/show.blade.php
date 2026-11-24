<x-app-layout :title="$business->name . ' — AI Builds My Website'">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ deleteModalOpen: false }">
        <!-- Top Breadcrumbs & Actions Header -->
        <div class="mb-6">
            <nav class="flex items-center text-xs font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-semibold">{{ $business->name }}</span>
            </nav>

            <div class="sm:flex sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl shadow-xs">
                        {{ strtoupper(substr($business->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $business->name }}</h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ ucwords(str_replace('_', ' ', $business->category)) }}
                            </span>
                            @if ($business->latestWebsite && $business->latestWebsite->is_published)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    Draft
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Created on {{ $business->created_at->format('M d, Y') }} • Last updated {{ $business->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 sm:mt-0 flex items-center gap-2.5">
                    <a href="{{ route('businesses.sources.index', $business) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Documents & Sources</span>
                    </a>

                    <a href="{{ route('businesses.facts.index', $business) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Verify Facts & Conflicts</span>
                    </a>

                    <a href="{{ route('businesses.edit', $business) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit Details</span>
                    </a>

                    <button type="button" @click="deleteModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Delete Project</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Business Information Summary -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Core Description Card -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-500 text-xs mb-3">About the Business</h2>
                    <p class="text-sm text-slate-700 leading-relaxed">
                        {{ $business->description ?: 'No detailed business description has been entered yet. Uploading a brochure or website link will automatically extract business summaries.' }}
                    </p>
                </div>

                <!-- Contact & Physical Address -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Contact & Location</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-xs font-medium text-slate-500">Phone Number</span>
                            <span class="font-semibold text-slate-900 mt-0.5 block">{{ $business->phone ?: 'Not provided' }}</span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium text-slate-500">Email Address</span>
                            <span class="font-semibold text-slate-900 mt-0.5 block">{{ $business->email ?: 'Not provided' }}</span>
                        </div>

                        <div class="sm:col-span-2">
                            <span class="block text-xs font-medium text-slate-500">Full Address</span>
                            <span class="font-semibold text-slate-900 mt-0.5 block">{{ $business->address ?: 'Not provided' }}</span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium text-slate-500">City / State</span>
                            <span class="font-semibold text-slate-900 mt-0.5 block">{{ $business->city ?: 'City unlisted' }}{{ $business->state ? ', ' . $business->state : '' }}</span>
                        </div>

                        <div>
                            <span class="block text-xs font-medium text-slate-500">Country</span>
                            <span class="font-semibold text-slate-900 mt-0.5 block">{{ $business->country ?: 'India' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Operations, Hours & Offerings -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Operations & Offerings</h2>
                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="block text-xs font-medium text-slate-500">Operating Hours</span>
                            <div class="mt-1 font-semibold text-slate-900">
                                @if (is_array($business->operating_hours) && isset($business->operating_hours['raw']))
                                    {{ $business->operating_hours['raw'] }}
                                @elseif (is_string($business->operating_hours))
                                    {{ $business->operating_hours }}
                                @else
                                    <span class="text-slate-400 font-normal">No operating hours specified</span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <span class="block text-xs font-medium text-slate-500 mb-1">Services List</span>
                                @if (is_array($business->services) && !empty($business->services))
                                    <ul class="list-disc list-inside space-y-1 text-xs text-slate-700">
                                        @foreach ($business->services as $service)
                                            <li>{{ is_string($service) ? $service : ($service['name'] ?? 'Service') }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-xs text-slate-400">No services specified</span>
                                @endif
                            </div>

                            <div>
                                <span class="block text-xs font-medium text-slate-500 mb-1">Products List</span>
                                @if (is_array($business->products) && !empty($business->products))
                                    <ul class="list-disc list-inside space-y-1 text-xs text-slate-700">
                                        @foreach ($business->products as $product)
                                            <li>{{ is_string($product) ? $product : ($product['name'] ?? 'Product') }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-xs text-slate-400">No products specified</span>
                                @endif
                            </div>
                        </div>

                        @if ($business->additional_info)
                            <div class="pt-2 border-t border-slate-100">
                                <span class="block text-xs font-medium text-slate-500 mb-1">Additional Notes</span>
                                <p class="text-xs text-slate-700 leading-relaxed">{{ $business->additional_info }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Col: Project Pipeline Status -->
            <div class="space-y-6">
                <!-- Pipeline Next Steps Card -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-sm font-bold text-slate-900 mb-1">Website Generation Pipeline</h2>
                    <p class="text-xs text-slate-500 mb-4">Phased workflow for transforming business data into a live website</p>

                    <div class="space-y-3">
                        <!-- Step 1: Profile Creation -->
                        <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50 flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                                ✓
                            </div>
                            <div>
                                <div class="text-xs font-bold text-emerald-900">Step 1: Business Profile Created</div>
                                <div class="text-[11px] text-emerald-700 mt-0.5">Core identity and contact details saved to MySQL.</div>
                            </div>
                        </div>

                        <!-- Step 2: Documents & Sources (Phase 3) -->
                        <a href="{{ route('businesses.sources.index', $business) }}"
                            class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-50 hover:border-blue-300 transition-all flex items-start gap-3 block group">
                            <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                                2
                            </div>
                            <div>
                                <div class="text-xs font-bold text-blue-900 group-hover:underline flex items-center gap-1.5">
                                    <span>Step 2: Upload Documents & Sources</span>
                                    <span>→</span>
                                </div>
                                <div class="text-[11px] text-blue-700 mt-0.5">Upload PDFs, menus, brochures, and web links (Active).</div>
                            </div>
                        </a>

                        <!-- Step 3: Fact Verification (Phase 4) -->
                        <a href="{{ route('businesses.facts.index', $business) }}"
                            class="p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-300 transition-all flex items-start gap-3 block group">
                            <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                                3
                            </div>
                            <div>
                                <div class="text-xs font-bold text-indigo-900 group-hover:underline flex items-center gap-1.5">
                                    <span>Step 3: Fact Verification & Conflicts</span>
                                    <span>→</span>
                                </div>
                                <div class="text-[11px] text-indigo-700 mt-0.5">Review facts, resolve conflicts, and confirm accuracy (Active).</div>
                            </div>
                        </a>

                        <!-- Step 4: AI Generation & Publishing (Phase 5-7) -->
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                                4
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800">Step 4: Generate & Publish Website</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Generate tailored sections and publish to a unique URL.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Source Counts Summary -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Project Assets Overview</h3>
                    <dl class="divide-y divide-slate-100 text-xs">
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Uploaded Documents</dt>
                            <dd class="font-bold text-slate-900">{{ $business->files->count() }} files</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Connected Sources</dt>
                            <dd class="font-bold text-slate-900">{{ $business->sources->count() }} URLs</dd>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <dt class="text-slate-500">Extracted Facts</dt>
                            <dd class="font-bold text-slate-900">{{ $business->extractedFacts->count() }} facts</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Alpine.js Delete Modal -->
        <div x-show="deleteModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="deleteModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="deleteModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                    <div class="sm:flex sm:items-start gap-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-rose-100 text-rose-600 sm:mx-0">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-bold text-slate-900" id="modal-title">Delete {{ $business->name }}?</h3>
                            <div class="mt-2 text-sm text-slate-500">
                                Are you sure you want to delete this business project? All associated uploaded files and website drafts will be removed.
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                        <form action="{{ route('businesses.destroy', $business) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-xs px-4 py-2.5 bg-rose-600 text-sm font-semibold text-white hover:bg-rose-700 transition-colors">
                                Delete Business
                            </button>
                        </form>
                        <button type="button" @click="deleteModalOpen = false"
                            class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-2xs px-4 py-2.5 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
