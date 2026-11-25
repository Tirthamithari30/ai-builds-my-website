<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsitePublishingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected WebsitePublishingService $publishingService
    ) {}

    /**
     * Public directory of all live published websites.
     */
    public function indexPublic(): View
    {
        $publishedWebsites = Website::where('is_published', true)
            ->with(['business', 'sections'])
            ->latest('published_at')
            ->get();

        return view('sites.index', [
            'websites' => $publishedWebsites,
        ]);
    }

    /**
     * Public endpoint to render live published websites for anonymous visitors.
     * Unpublished drafts strictly return a 404 HTTP status.
     */
    public function showPublic(string $slug): View
    {
        $website = Website::where('slug', $slug)->first();

        if (!$website || !$website->is_published) {
            abort(404, 'The requested website does not exist or has not been published yet.');
        }

        $website->load([
            'business',
            'sections' => fn($q) => $q->where('is_visible', true)->orderBy('order', 'asc'),
        ]);

        $preset = WebsiteGenerationService::PRESETS[$website->design_preset]
            ?? WebsiteGenerationService::PRESETS['modern_minimal'];

        return view('templates.index', [
            'website' => $website,
            'business' => $website->business,
            'sections' => $website->sections,
            'preset' => $preset,
            'isPublic' => true,
        ]);
    }

    /**
     * Publish website live with pre-flight fact validation.
     */
    public function publish(Request $request, Website $website): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $website);

        $validation = $this->publishingService->validateForPublishing($website);
        if (!$validation['is_valid']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validation['errors'],
                ], 422);
            }
            return back()->withErrors($validation['errors'])->with('error', 'Cannot publish website: ' . implode(' ', $validation['errors']));
        }

        try {
            $publication = $this->publishingService->publish($website, auth()->id());
            $publicUrl = $publication->published_url;

            $msg = "Website published successfully! Live at: {$publicUrl}";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'published_url' => $publicUrl,
                    'published_at' => $publication->published_at->toIso8601String(),
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Publishing failed: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish website, taking it offline immediately from anonymous visitors.
     */
    public function unpublish(Request $request, Website $website): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $website);

        $this->publishingService->unpublish($website);
        $msg = 'Website has been taken offline. It is now a private draft.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'is_published' => false,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Update public vanity slug with collision check and reserved keyword enforcement.
     */
    public function updateSlug(Request $request, Website $website): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'slug' => 'required|string|min:3|max:80',
        ]);

        try {
            $this->publishingService->updateSlug($website, $validated['slug'], auth()->id());
            $msg = "Website URL slug updated to: /sites/{$website->slug}";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'slug' => $website->slug,
                    'public_url' => url("/sites/{$website->slug}"),
                ]);
            }

            return back()->with('success', $msg);
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['slug' => $e->getMessage()]);
        }
    }

    /**
     * Update SEO and Social Sharing (Open Graph) metadata.
     */
    public function updateSeo(Request $request, Website $website): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'seo_title' => 'nullable|string|max:120',
            'meta_description' => 'required|string|max:200',
            'og_image' => 'nullable|url|max:255',
        ]);

        $settings = $website->settings ?? [];
        if (!empty($validated['seo_title'])) {
            $settings['seo_title'] = $validated['seo_title'];
        } else {
            unset($settings['seo_title']);
        }

        if (!empty($validated['og_image'])) {
            $settings['og_image'] = $validated['og_image'];
        } else {
            unset($settings['og_image']);
        }

        $website->update([
            'meta_description' => $validated['meta_description'],
            'settings' => $settings,
        ]);

        $this->publishingService->createSnapshot($website, "Updated SEO and social metadata", auth()->id());

        $msg = 'SEO and social sharing metadata updated successfully.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
