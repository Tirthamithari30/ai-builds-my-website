<x-app-layout :title="'Sources & Documents — ' . $business->name">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        x-data="{
            previewModalOpen: false,
            previewTitle: '',
            previewContent: '',
            previewSubtitle: '',
            editSourceModalOpen: false,
            editSourceUrl: '',
            editSourceType: '',
            editActionUrl: '',
            deleteModalOpen: false,
            deleteActionUrl: '',
            deleteTargetName: ''
        }">

        <!-- Top Breadcrumbs & Header -->
        <div class="mb-6">
            <nav class="flex items-center text-xs font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('businesses.show', $business) }}" class="hover:text-slate-800">{{ $business->name }}</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-semibold">Sources & Documents</span>
            </nav>

            <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Documents & Sources</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            Phase 3
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Upload brochures, price lists, menus, or provide public URLs for <strong>{{ $business->name }}</strong>. All files and pages are parsed without inventing facts.
                    </p>
                </div>

                <div class="mt-4 sm:mt-0 flex items-center gap-2">
                    <a href="{{ route('businesses.show', $business) }}"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                        ← Back to Profile
                    </a>
                </div>
            </div>
        </div>

        @if (!$searchAvailable)
            <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3 text-amber-900 text-xs">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-bold">Automated Search Engine Unconfigured:</span>
                    <span>The web search API key is not currently set in the environment. Automated web discovery is disabled, but you can directly add website URLs, Google Business links, or upload documents below.</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column: Document Uploads -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <div class="border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">1. Upload Documents & Photos</h2>
                            <p class="text-xs text-slate-500">PDFs, Word documents, text files, or photos (Max 15MB)</p>
                        </div>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                            {{ $files->count() }} Files
                        </span>
                    </div>

                    <!-- Upload Form -->
                    <form method="POST" action="{{ route('businesses.files.store', $business) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Choose File</label>
                            <input type="file" name="file" required
                                accept=".pdf,.docx,.txt,.jpg,.jpeg,.png,.webp"
                                class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50">
                            <p class="text-[11px] text-slate-400 mt-1">Supported: PDF, DOCX, TXT, JPG, PNG, WebP</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span>Upload & Process</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Uploaded Files List -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Uploaded Files</h3>
                        <span class="text-xs text-slate-500">{{ $files->count() }} total</span>
                    </div>

                    @if ($files->isEmpty())
                        <div class="p-8 text-center">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">No documents uploaded yet</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Upload a PDF price list, menu, or brochure to automatically extract facts.</p>
                        </div>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($files as $file)
                                <li class="p-4 hover:bg-slate-50/70 transition-colors flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <!-- File Type Badge -->
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-[10px] uppercase flex-shrink-0
                                            {{ $file->file_type === 'pdf' ? 'bg-rose-100 text-rose-700' : '' }}
                                            {{ $file->file_type === 'docx' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $file->file_type === 'txt' ? 'bg-slate-100 text-slate-700' : '' }}
                                            {{ in_array($file->file_type, ['image', 'jpg', 'jpeg', 'png', 'webp']) ? 'bg-emerald-100 text-emerald-700' : '' }}">
                                            {{ $file->file_type }}
                                        </div>

                                        <div>
                                            <div class="text-xs font-bold text-slate-900 break-all">
                                                {{ $file->original_name }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                                <span>{{ number_format($file->file_size / 1024, 1) }} KB</span>
                                                <span>•</span>
                                                <span>{{ $file->created_at->format('M d, H:i') }}</span>
                                            </div>

                                            <!-- Status Indicator -->
                                            <div class="mt-2">
                                                @if ($file->processing_status === 'completed')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Text Extracted
                                                    </span>
                                                @elseif ($file->processing_status === 'scanned_ocr_required')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="{{ $file->error_message }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        Scanned (OCR not available)
                                                    </span>
                                                @elseif ($file->processing_status === 'failed')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $file->error_message }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Failed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                        Processing...
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if ($file->extracted_text)
                                            <button type="button"
                                                @click="previewModalOpen = true; previewTitle = '{{ addslashes($file->original_name) }}'; previewSubtitle = 'Extracted Document Content'; previewContent = `{{ addslashes($file->extracted_text) }}`"
                                                class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                                Preview
                                            </button>
                                        @endif

                                        <form method="POST" action="{{ route('files.retry', $file) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="Retry Extraction">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                        </form>

                                        <button type="button"
                                            @click="deleteModalOpen = true; deleteActionUrl = '{{ route('files.destroy', $file) }}'; deleteTargetName = '{{ addslashes($file->original_name) }}'"
                                            class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" title="Delete File">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Right Column: Online Sources -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                    <div class="border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">2. Connect Online Sources</h2>
                            <p class="text-xs text-slate-500">Website, Google Business listing, or social profiles</p>
                        </div>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                            {{ $sources->count() }} Sources
                        </span>
                    </div>

                    <!-- Add Source URL Form -->
                    <form method="POST" action="{{ route('businesses.sources.store', $business) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Source Type</label>
                                <select name="source_type" required
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="url">Website URL</option>
                                    <option value="google_business">Google Business Profile</option>
                                    <option value="instagram">Instagram Profile</option>
                                    <option value="facebook">Facebook Page</option>
                                    <option value="linkedin">LinkedIn Page</option>
                                    <option value="youtube">YouTube Channel</option>
                                    <option value="other">Other Public Link</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Webpage or Profile URL</label>
                                <input type="url" name="url" required
                                    placeholder="https://example.com"
                                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <span>Crawl Source URL</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sources List -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Connected Sources</h3>
                        <span class="text-xs text-slate-500">{{ $sources->count() }} total</span>
                    </div>

                    @if ($sources->isEmpty())
                        <div class="p-8 text-center">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-slate-700">No external sources connected</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Add your website or Google listing to automatically extract public facts.</p>
                        </div>
                    @else
                        <ul class="divide-y divide-slate-100">
                            @foreach ($sources as $source)
                                <li class="p-4 hover:bg-slate-50/70 transition-colors flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <!-- Source Type Badge -->
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            @if ($source->source_type === 'google_business')
                                                G
                                            @elseif ($source->source_type === 'instagram')
                                                IG
                                            @elseif ($source->source_type === 'facebook')
                                                FB
                                            @elseif ($source->source_type === 'linkedin')
                                                IN
                                            @elseif ($source->source_type === 'youtube')
                                                YT
                                            @else
                                                URL
                                            @endif
                                        </div>

                                        <div>
                                            <div class="text-xs font-bold text-slate-900 break-all">
                                                {{ $source->title ?: $source->url }}
                                            </div>
                                            <a href="{{ $source->url }}" target="_blank" rel="noopener noreferrer" class="text-[11px] text-blue-600 hover:underline break-all block mt-0.5">
                                                {{ $source->url }}
                                            </a>

                                            <!-- Status Indicator -->
                                            <div class="mt-2 flex items-center gap-2">
                                                @if ($source->status === 'crawled')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Crawled & Extracted
                                                    </span>
                                                @elseif ($source->status === 'blocked')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="{{ $source->error_message }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        Access Blocked by Platform
                                                    </span>
                                                @elseif ($source->status === 'failed')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $source->error_message }}">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Crawl Failed
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                        Pending...
                                                    </span>
                                                @endif

                                                @if ($source->crawled_at)
                                                    <span class="text-[11px] text-slate-400">
                                                        {{ $source->crawled_at->format('M d, H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if ($source->raw_content)
                                            <button type="button"
                                                @click="previewModalOpen = true; previewTitle = '{{ addslashes($source->title ?: $source->url) }}'; previewSubtitle = 'Extracted Webpage Content'; previewContent = `{{ addslashes($source->raw_content) }}`"
                                                class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                                                Preview
                                            </button>
                                        @endif

                                        <button type="button"
                                            @click="editSourceModalOpen = true; editSourceUrl = '{{ addslashes($source->url) }}'; editSourceType = '{{ $source->source_type }}'; editActionUrl = '{{ route('sources.update', $source) }}'"
                                            class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="Edit Source">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        <form method="POST" action="{{ route('sources.retry', $source) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="Retry Crawl">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                        </form>

                                        <button type="button"
                                            @click="deleteModalOpen = true; deleteActionUrl = '{{ route('sources.destroy', $source) }}'; deleteTargetName = '{{ addslashes($source->title ?: $source->url) }}'"
                                            class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" title="Delete Source">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alpine.js Preview Modal -->
        <div x-show="previewModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="previewModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="previewModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="previewModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" x-text="previewTitle"></h3>
                            <p class="text-xs text-slate-500" x-text="previewSubtitle"></p>
                        </div>
                        <button type="button" @click="previewModalOpen = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4 max-h-96 overflow-y-auto bg-slate-50 p-4 rounded-xl text-xs font-mono text-slate-700 whitespace-pre-wrap leading-relaxed border border-slate-200"
                        x-text="previewContent">
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button" @click="previewModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                            Close Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alpine.js Edit Source Modal -->
        <div x-show="editSourceModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editSourceModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="editSourceModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editSourceModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                    <form :action="editActionUrl" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <h3 class="text-base font-bold text-slate-900">Edit Source URL</h3>
                            <p class="text-xs text-slate-500">Update the URL and source type to re-crawl</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Source Type</label>
                            <select name="source_type" x-model="editSourceType" required
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="url">Website URL</option>
                                <option value="google_business">Google Business Profile</option>
                                <option value="instagram">Instagram Profile</option>
                                <option value="facebook">Facebook Page</option>
                                <option value="linkedin">LinkedIn Page</option>
                                <option value="youtube">YouTube Channel</option>
                                <option value="other">Other Public Link</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">URL</label>
                            <input type="url" name="url" x-model="editSourceUrl" required
                                class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="editSourceModalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-xs">
                                Update & Re-crawl
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alpine.js Delete Confirmation Modal -->
        <div x-show="deleteModalOpen" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="deleteModalOpen" x-transition.opacity
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="deleteModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteModalOpen" x-transition.scale
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <div class="sm:flex sm:items-start gap-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-xl bg-rose-100 text-rose-600 sm:mx-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:text-left">
                            <h3 class="text-base font-bold text-slate-900">Confirm Deletion</h3>
                            <div class="mt-1 text-xs text-slate-500">
                                Are you sure you want to delete <span class="font-bold text-slate-800" x-text="deleteTargetName"></span>?
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:flex sm:flex-row-reverse gap-2">
                        <form :action="deleteActionUrl" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl px-4 py-2 bg-rose-600 text-xs font-semibold text-white hover:bg-rose-700 transition-colors">
                                Delete Permanently
                            </button>
                        </form>
                        <button type="button" @click="deleteModalOpen = false"
                            class="mt-2 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
