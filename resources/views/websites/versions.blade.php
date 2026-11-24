<x-app-layout :title="'Version History — ' . $website->title">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center text-xs font-medium text-slate-500 mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="{{ route('businesses.show', $business) }}" class="hover:text-slate-800">{{ $business->name }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('websites.preview', $website) }}" class="hover:text-slate-800">Preview</a>
            <span class="mx-2">/</span>
            <span class="text-slate-900 font-semibold">Version History</span>
        </nav>

        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between pb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Version History: {{ $website->title }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Immutable snapshots created whenever a website is generated, modified, or restored.
                </p>
            </div>

            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                <a href="{{ route('websites.preview', $website) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back to Live Preview</span>
                </a>
            </div>
        </div>

        <!-- Versions List Card -->
        <div class="mt-8 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if ($versions->isEmpty())
                <div class="p-12 text-center text-slate-500 text-sm">
                    No versions found.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($versions as $ver)
                        @php
                            $isCurrent = $ver->version_number === $website->current_version;
                            $snap = $ver->snapshot ?? [];
                            $sectionCount = count($snap['sections'] ?? []);
                        @endphp
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 {{ $isCurrent ? 'bg-blue-50/30' : 'hover:bg-slate-50/70' }} transition-colors">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base font-extrabold text-slate-900">
                                        Version {{ $ver->version_number }}
                                    </span>
                                    @if ($isCurrent)
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                            Current Active Version
                                        </span>
                                    @endif
                                    <span class="text-xs text-slate-400 font-mono">
                                        {{ $sectionCount }} sections
                                    </span>
                                </div>

                                <p class="text-xs text-slate-600">
                                    {{ $ver->changelog ?: 'Automatic generation snapshot' }}
                                </p>

                                <div class="text-[11px] text-slate-400 flex items-center gap-3">
                                    <span>Created by {{ $ver->creator->name ?? 'User' }}</span>
                                    <span>•</span>
                                    <span>{{ $ver->created_at->format('M d, Y \a\t h:i A') }} ({{ $ver->created_at->diffForHumans() }})</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                @if (!$isCurrent)
                                    <form method="POST" action="{{ route('websites.versions.restore', [$website, $ver]) }}"
                                          onsubmit="return confirm('Restore website to Version #{{ $ver->version_number }} snapshot? Current active sections will be replaced.');">
                                        @csrf
                                        <button type="submit"
                                                class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors">
                                            Restore Version
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('websites.preview', $website) }}"
                                       class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
                                        View in Preview
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
