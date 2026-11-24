<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Services\SourceExtractionService;
use App\Services\WebResearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BusinessSourceController extends Controller
{
    public function __construct(
        protected SourceExtractionService $extractionService,
        protected WebResearchService $webService
    ) {}

    /**
     * Display the business Sources and Documents management screen.
     */
    public function index(Business $business): View
    {
        Gate::authorize('view', $business);

        $sources = $business->sources()->latest()->get();
        $files = $business->files()->latest()->get();

        return view('businesses.sources.index', [
            'business' => $business,
            'sources' => $sources,
            'files' => $files,
            'searchAvailable' => $this->webService->isSearchConfigured(),
        ]);
    }

    /**
     * Store a newly created source URL and trigger collection.
     */
    public function store(Request $request, Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        $validated = $request->validate([
            'url' => ['required', 'string', 'max:1000'],
            'source_type' => ['required', 'string', 'in:url,google_business,instagram,facebook,linkedin,youtube,other'],
        ], [
            'url.required' => 'Please provide a valid webpage or profile URL.',
            'source_type.required' => 'Please select the type of source.',
        ]);

        $url = trim($validated['url']);

        // Check SSRF
        $ssrf = $this->webService->validateSafeUrl($url);
        if (!$ssrf['safe']) {
            return back()->withInput()->with('error', 'Security check failed: ' . $ssrf['reason']);
        }

        $source = BusinessSource::create([
            'business_id' => $business->id,
            'source_type' => $validated['source_type'],
            'url' => $url,
            'status' => 'pending',
        ]);

        $result = $this->extractionService->processSource($source);

        if ($result['status'] === 'blocked') {
            return redirect()->route('businesses.sources.index', $business)
                ->with('warning', 'Source added, but the external platform blocked automated reading. Please enter any critical details manually.');
        }

        if (!$result['success']) {
            return redirect()->route('businesses.sources.index', $business)
                ->with('error', 'Source saved, but crawling failed: ' . ($result['error'] ?? 'Unreachable'));
        }

        return redirect()->route('businesses.sources.index', $business)
            ->with('success', 'Source crawled and business facts extracted successfully!');
    }

    /**
     * Update an existing source URL and retry collection.
     */
    public function update(Request $request, BusinessSource $source): RedirectResponse
    {
        Gate::authorize('update', $source);

        $validated = $request->validate([
            'url' => ['required', 'string', 'max:1000'],
            'source_type' => ['required', 'string', 'in:url,google_business,instagram,facebook,linkedin,youtube,other'],
        ]);

        $url = trim($validated['url']);

        // Check SSRF
        $ssrf = $this->webService->validateSafeUrl($url);
        if (!$ssrf['safe']) {
            return back()->withInput()->with('error', 'Security check failed: ' . $ssrf['reason']);
        }

        $source->update([
            'url' => $url,
            'source_type' => $validated['source_type'],
            'status' => 'pending',
            'error_message' => null,
        ]);

        $this->extractionService->processSource($source);

        return redirect()->route('businesses.sources.index', $source->business)
            ->with('success', 'Source updated and recollected.');
    }

    /**
     * Retry collecting a source.
     */
    public function retry(BusinessSource $source): RedirectResponse
    {
        Gate::authorize('update', $source);

        $result = $this->extractionService->processSource($source);

        if ($result['status'] === 'blocked') {
            return back()->with('warning', 'Retry attempted: the external platform continues to block automated reading.');
        }

        if (!$result['success']) {
            return back()->with('error', 'Retry failed: ' . ($result['error'] ?? 'Unreachable URL'));
        }

        return back()->with('success', 'Source recollected successfully.');
    }

    /**
     * Remove the specified source.
     */
    public function destroy(BusinessSource $source): RedirectResponse
    {
        Gate::authorize('delete', $source);

        $business = $source->business;
        $source->delete();

        return redirect()->route('businesses.sources.index', $business)
            ->with('success', 'Source removed successfully.');
    }
}
