<x-app-layout title="Dashboard — AI Builds My Website">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ deleteModalOpen: false, deleteActionUrl: '', deleteBusinessName: '' }">
        <!-- Welcome Header Banner -->
        <div class="sm:flex sm:items-center sm:justify-between pb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage your business projects, verify collected facts, and publish authentic AI-generated websites.
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                <a href="{{ route('businesses.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Create New Website</span>
                </a>
            </div>
        </div>

        <!-- Real Database Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <span>Saved Projects</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['total_businesses'] }}</div>
                <div class="mt-1 text-xs text-slate-500">Total active business profiles</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <span>Published Websites</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['published_websites'] }}</div>
                <div class="mt-1 text-xs text-slate-500">Live at unique public URLs</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <span>Draft Websites</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['draft_websites'] }}</div>
                <div class="mt-1 text-xs text-slate-500">Private work in progress</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <span>Extracted Facts</span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                </div>
                <div class="mt-3 text-3xl font-extrabold text-slate-900">{{ $stats['total_facts'] }}</div>
                <div class="mt-1 text-xs text-slate-500">From uploaded docs & sources</div>
            </div>
        </div>

        <!-- Search, Filter & Actions Toolbar -->
        <div class="mt-8 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5 relative">
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                        placeholder="Search projects by business name or city..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="sm:col-span-3">
                    <select name="category" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">All Categories</option>
                        <option value="restaurant" {{ $filters['category'] === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="cafe" {{ $filters['category'] === 'cafe' ? 'selected' : '' }}>Cafe</option>
                        <option value="salon" {{ $filters['category'] === 'salon' ? 'selected' : '' }}>Salon & Spa</option>
                        <option value="retail" {{ $filters['category'] === 'retail' ? 'selected' : '' }}>Retail Store</option>
                        <option value="consultant" {{ $filters['category'] === 'consultant' ? 'selected' : '' }}>Consulting</option>
                        <option value="fitness" {{ $filters['category'] === 'fitness' ? 'selected' : '' }}>Fitness Centre</option>
                        <option value="professional_services" {{ $filters['category'] === 'professional_services' ? 'selected' : '' }}>Professional Services</option>
                        <option value="other" {{ $filters['category'] === 'other' ? 'selected' : '' }}>Other Business</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <select name="status" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">All Statuses</option>
                        <option value="published" {{ $filters['status'] === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-3 text-xs font-semibold rounded-xl bg-slate-900 text-white hover:bg-slate-800 transition-colors shadow-2xs">
                        Filter
                    </button>
                    @if($filters['search'] || $filters['category'] || $filters['status'])
                        <a href="{{ route('dashboard') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors" title="Clear Filters">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Project List Section -->
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900">Your Business Projects</h2>
                <span class="text-xs text-slate-500 font-medium">Showing {{ $businesses->count() }} of {{ $stats['total_businesses'] }} total projects</span>
            </div>

            @if ($businesses->isEmpty())
                <!-- Empty State -->
                <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center shadow-xs">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>

                    @if ($filters['search'] || $filters['category'] || $filters['status'])
                        <h3 class="text-base font-bold text-slate-900">No matching projects found</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            No business matches your search query. Try resetting your search filters to see all projects.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                Clear Search Filters
                            </a>
                        </div>
                    @else
                        <h3 class="text-base font-bold text-slate-900">No business projects yet</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                            Get started by creating your first business profile. You can enter information, upload price lists/PDFs, and generate a verified website.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('businesses.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Create Your First Business</span>
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <!-- Projects Table Card -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5">Business Name</th>
                                    <th scope="col" class="px-6 py-3.5">Category</th>
                                    <th scope="col" class="px-6 py-3.5">Location</th>
                                    <th scope="col" class="px-6 py-3.5">Website Status</th>
                                    <th scope="col" class="px-6 py-3.5">Last Updated</th>
                                    <th scope="col" class="px-6 py-3.5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($businesses as $b)
                                    @php
                                        $site = $b->latestWebsite;
                                        $isPublished = $site && $site->is_published;
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <!-- Business Name & Description -->
                                        <td class="px-6 py-4">
                                            <a href="{{ route('businesses.show', $b) }}" class="font-bold text-slate-900 hover:text-blue-600 transition-colors flex items-center gap-2">
                                                <span>{{ $b->name }}</span>
                                            </a>
                                            <div class="text-xs text-slate-500 line-clamp-1 mt-0.5">
                                                {{ $b->description ?: 'No description provided' }}
                                            </div>
                                        </td>

                                        <!-- Category Badge -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                {{ ucwords(str_replace('_', ' ', $b->category)) }}
                                            </span>
                                        </td>

                                        <!-- Location -->
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                                            {{ $b->city ?: 'City unlisted' }}{{ $b->state ? ', ' . $b->state : '' }}
                                        </td>

                                        <!-- Website Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($isPublished)
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span>Published</span>
                                                </div>
                                            @else
                                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    <span>Draft</span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Last Updated -->
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                            {{ $b->updated_at->format('M d, Y') }}
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                            @if ($site)
                                                <a href="{{ route('websites.edit', $site) }}"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors font-semibold">
                                                    Editor
                                                </a>
                                                <a href="{{ route('websites.preview', $site) }}"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors font-semibold">
                                                    Preview
                                                </a>
                                            @else
                                                <a href="{{ route('businesses.website.setup', $b) }}"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors font-semibold">
                                                    Generate
                                                </a>
                                            @endif

                                            <a href="{{ route('businesses.show', $b) }}"
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors font-semibold">
                                                View
                                            </a>

                                            <a href="{{ route('businesses.edit', $b) }}"
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors font-semibold">
                                                Edit
                                            </a>

                                            <button type="button"
                                                @click="deleteModalOpen = true; deleteActionUrl = '{{ route('businesses.destroy', $b) }}'; deleteBusinessName = '{{ addslashes($b->name) }}'"
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors font-semibold">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($businesses->hasPages())
                        <div class="p-4 border-t border-slate-200">
                            {{ $businesses->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Alpine.js Delete Confirmation Modal -->
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
                            <h3 class="text-lg font-bold text-slate-900" id="modal-title">Delete Business Project</h3>
                            <div class="mt-2 text-sm text-slate-500">
                                Are you sure you want to delete <span class="font-bold text-slate-800" x-text="deleteBusinessName"></span>? This will remove the business project and its associated documents, sources, and website drafts.
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                        <form :action="deleteActionUrl" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-xs px-4 py-2.5 bg-rose-600 text-sm font-semibold text-white hover:bg-rose-700 transition-colors">
                                Confirm Delete
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
