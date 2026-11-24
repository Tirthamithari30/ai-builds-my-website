<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Models\WebsiteVersion;
use App\Services\FactVerificationService;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebsiteGenerationController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected WebsiteGenerationService $websiteService,
        protected FactVerificationService $factService,
        protected WebsitePublishingService $publishingService
    ) {}

    /**
     * Show Pre-Generation Review screen with verified facts, conflicts check, and style options.
     */
    public function setup(Business $business): View
    {
        $this->authorize('view', $business);

        $factAnalysis = $this->factService->analyzeFacts($business);
        $verifiedDict = $this->factService->getVerifiedDictionary($business);

        // Uploaded business images (logos, photos, etc.)
        $imageFiles = $business->files()
            ->where(function ($q) {
                $q->where('mime_type', 'like', 'image/%')
                    ->orWhere('original_name', 'like', '%.jpg')
                    ->orWhere('original_name', 'like', '%.jpeg')
                    ->orWhere('original_name', 'like', '%.png')
                    ->orWhere('original_name', 'like', '%.webp');
            })
            ->get();

        $presets = WebsiteGenerationService::PRESETS;
        $websiteTypes = WebsiteGenerationService::WEBSITE_TYPES;
        $allowedSections = WebsiteGenerationService::ALLOWED_SECTIONS;
        $defaultSections = $this->websiteService->determineSectionsForCategory($business->category);
        $existingWebsite = $business->latestWebsite;

        return view('websites.setup', [
            'business' => $business,
            'factAnalysis' => $factAnalysis,
            'verifiedDict' => $verifiedDict,
            'imageFiles' => $imageFiles,
            'presets' => $presets,
            'websiteTypes' => $websiteTypes,
            'allowedSections' => $allowedSections,
            'defaultSections' => $defaultSections,
            'existingWebsite' => $existingWebsite,
            'hasConflicts' => $factAnalysis['has_blocking_conflicts'],
        ]);
    }

    /**
     * Generate website based on verified facts and user configuration.
     */
    public function generate(Request $request, Business $business): RedirectResponse
    {
        $this->authorize('update', $business);

        // Check for blocking conflicts before any processing
        $factAnalysis = $this->factService->analyzeFacts($business);
        if ($factAnalysis['has_blocking_conflicts']) {
            return redirect()->route('businesses.facts.index', $business)
                ->with('error', 'Cannot generate website while unresolved conflicting facts exist. Please resolve all conflicts first.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'website_type' => 'required|string|in:' . implode(',', array_keys(WebsiteGenerationService::WEBSITE_TYPES)),
            'design_preset' => 'required|string|in:' . implode(',', array_keys(WebsiteGenerationService::PRESETS)),
            'primary_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'sections' => 'required|array|min:1',
            'sections.*' => 'string|in:' . implode(',', array_keys(WebsiteGenerationService::ALLOWED_SECTIONS)),
            'custom_instructions' => 'nullable|string|max:1000',
        ]);

        try {
            $website = $this->websiteService->generateWebsite($business, $validated);

            $mode = $website->settings['generation_mode'] ?? 'generated';
            $msg = ($mode === 'gemini_ai')
                ? 'Website successfully generated with Gemini AI using confirmed business facts!'
                : 'Website successfully generated from verified business facts!';

            return redirect()->route('websites.preview', $website)->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('businesses.facts.index', $business)->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Generation error: ' . $e->getMessage());
        }
    }

    /**
     * Show live interactive preview screen with responsive device switchers.
     */
    public function preview(Website $website): View
    {
        $this->authorize('view', $website);

        $website->load(['business', 'sections', 'versions']);

        return view('websites.preview', [
            'website' => $website,
            'business' => $website->business,
            'sections' => $website->sections,
            'versions' => $website->versions,
        ]);
    }

    /**
     * Standalone rendered website inside the preview iframe.
     */
    public function previewFrame(Website $website): View
    {
        $this->authorize('view', $website);

        $website->load(['business', 'sections' => fn($q) => $q->where('is_visible', true)->orderBy('order', 'asc')]);

        return view('templates.index', [
            'website' => $website,
            'business' => $website->business,
            'sections' => $website->sections,
            'preset' => WebsiteGenerationService::PRESETS[$website->design_preset] ?? WebsiteGenerationService::PRESETS['modern_minimal'],
        ]);
    }

    /**
     * Show immutable version snapshot history for this website.
     */
    public function versions(Website $website): View
    {
        $this->authorize('view', $website);

        $website->load(['business', 'versions' => fn($q) => $q->orderBy('version_number', 'desc'), 'versions.creator']);

        return view('websites.versions', [
            'website' => $website,
            'business' => $website->business,
            'versions' => $website->versions,
        ]);
    }

    /**
     * Restore a previous immutable snapshot version.
     */
    public function restoreVersion(Website $website, WebsiteVersion $version): RedirectResponse
    {
        $this->authorize('update', $website);

        if ($version->website_id !== $website->id) {
            abort(404, 'Version does not belong to this website.');
        }

        $snapshot = $version->snapshot;
        if (!is_array($snapshot) || !isset($snapshot['sections'])) {
            return back()->with('error', 'Invalid version snapshot.');
        }

        DB::transaction(function () use ($website, $version, $snapshot) {
            // Restore website settings and styling
            $website->update([
                'title' => $snapshot['title'] ?? $website->title,
                'design_preset' => $snapshot['design_preset'] ?? $website->design_preset,
                'primary_color' => $snapshot['primary_color'] ?? $website->primary_color,
                'secondary_color' => $snapshot['secondary_color'] ?? $website->secondary_color,
                'font_family' => $snapshot['font_family'] ?? $website->font_family,
                'meta_description' => $snapshot['meta_description'] ?? $website->meta_description,
                'settings' => $snapshot['settings'] ?? $website->settings,
            ]);

            // Replace sections
            $website->sections()->delete();

            $order = 0;
            foreach ($snapshot['sections'] as $sec) {
                WebsiteSection::create([
                    'website_id' => $website->id,
                    'section_type' => $sec['section_type'],
                    'order' => $sec['order'] ?? $order++,
                    'is_visible' => $sec['is_visible'] ?? true,
                    'content' => $sec['content'] ?? [],
                    'design_overrides' => $sec['design_overrides'] ?? null,
                ]);
            }

            // Create new snapshot reflecting the restore
            $this->publishingService->createSnapshot(
                $website,
                "Restored snapshot from Version #{$version->version_number}",
                auth()->id()
            );
        });

        return redirect()->route('websites.preview', $website)
            ->with('success', "Website restored to Version #{$version->version_number} snapshot.");
    }
}
