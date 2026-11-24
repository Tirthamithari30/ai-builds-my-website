<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\WebsiteSection;
use App\Models\WebsiteVersion;
use App\Services\FactVerificationService;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebsiteEditorController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected WebsiteGenerationService $websiteService,
        protected FactVerificationService $factService,
        protected WebsitePublishingService $publishingService
    ) {}

    /**
     * Show Visual Website Editor with live preview canvas, editing sidebar, and controls.
     */
    public function edit(Website $website): View
    {
        $this->authorize('update', $website);

        $website->load([
            'business.files' => function ($q) {
                $q->where(function ($sub) {
                    $sub->where('mime_type', 'like', 'image/%')
                        ->orWhere('original_name', 'like', '%.jpg')
                        ->orWhere('original_name', 'like', '%.jpeg')
                        ->orWhere('original_name', 'like', '%.png')
                        ->orWhere('original_name', 'like', '%.webp');
                });
            },
            'sections' => fn($q) => $q->orderBy('order', 'asc'),
            'versions' => fn($q) => $q->orderBy('version_number', 'desc'),
        ]);

        $presets = WebsiteGenerationService::PRESETS;
        $allowedSections = WebsiteGenerationService::ALLOWED_SECTIONS;
        $verifiedDict = $this->factService->getVerifiedDictionary($website->business);

        $fonts = [
            'Inter, system-ui, sans-serif' => 'Inter (Modern Sans-serif)',
            "'Playfair Display', Georgia, serif" => 'Playfair Display (Refined Serif)',
            "'Plus Jakarta Sans', system-ui, sans-serif" => 'Plus Jakarta Sans (Expressive Geometric)',
            "system-ui, -apple-system, sans-serif" => 'System Native',
        ];

        $publishingValidation = $this->publishingService->validateForPublishing($website);

        return view('websites.editor', [
            'website' => $website,
            'business' => $website->business,
            'sections' => $website->sections,
            'versions' => $website->versions,
            'presets' => $presets,
            'allowedSections' => $allowedSections,
            'verifiedDict' => $verifiedDict,
            'fonts' => $fonts,
            'uploadedImages' => $website->business->files,
            'publishingValidation' => $publishingValidation,
        ]);
    }

    /**
     * Add a new section to the website.
     */
    public function addSection(Request $request, Website $website): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'section_type' => 'required|string|in:' . implode(',', array_keys(WebsiteGenerationService::ALLOWED_SECTIONS)),
        ]);

        $type = $validated['section_type'];
        $verifiedDict = $this->factService->getVerifiedDictionary($website->business);

        $content = $this->websiteService->buildSectionContent(
            type: $type,
            business: $website->business,
            verifiedFacts: $verifiedDict,
            preset: $website->design_preset
        );

        $nextOrder = ($website->sections()->max('order') ?? -1) + 1;

        $section = WebsiteSection::create([
            'website_id' => $website->id,
            'section_type' => $type,
            'order' => $nextOrder,
            'is_visible' => true,
            'content' => $content,
            'design_overrides' => [
                'is_manually_added' => true,
                'created_at' => now()->toIso8601String(),
            ],
        ]);

        $this->publishingService->createSnapshot(
            $website,
            "Added " . (WebsiteGenerationService::ALLOWED_SECTIONS[$type] ?? $type) . " section",
            auth()->id()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Section added successfully',
                'section' => $section,
            ]);
        }

        return redirect()->route('websites.edit', $website)->with('success', 'Section added successfully.');
    }

    /**
     * Update an individual section's content and visibility.
     */
    public function updateSection(Request $request, WebsiteSection $section): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $section->website);

        $validated = $request->validate([
            'content' => 'required|array',
            'is_visible' => 'nullable|boolean',
        ]);

        $overrides = $section->design_overrides ?? [];
        $overrides['is_manually_edited'] = true;
        $overrides['last_edited_at'] = now()->toIso8601String();

        $section->update([
            'content' => $validated['content'],
            'is_visible' => $request->has('is_visible') ? (bool)$validated['is_visible'] : $section->is_visible,
            'design_overrides' => $overrides,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Section content updated',
                'section' => $section->fresh(),
            ]);
        }

        return redirect()->route('websites.edit', $section->website)->with('success', 'Section updated successfully.');
    }

    /**
     * Remove a section from the website.
     */
    public function deleteSection(Request $request, WebsiteSection $section): JsonResponse|RedirectResponse
    {
        $website = $section->website;
        $this->authorize('update', $website);

        // Minimum 1 section required
        if ($website->sections()->count() <= 1) {
            $msg = 'A website must retain at least one section.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $type = $section->section_type;

        DB::transaction(function () use ($website, $section, $type) {
            $section->delete();

            // Re-index remaining section orders
            $remaining = $website->sections()->orderBy('order', 'asc')->get();
            foreach ($remaining as $idx => $s) {
                $s->update(['order' => $idx]);
            }

            $this->publishingService->createSnapshot(
                $website,
                "Removed {$type} section",
                auth()->id()
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Section removed successfully',
            ]);
        }

        return redirect()->route('websites.edit', $website)->with('success', 'Section removed successfully.');
    }

    /**
     * Toggle visibility of a section (Hide / Show).
     */
    public function toggleVisibility(Request $request, WebsiteSection $section): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $section->website);

        $section->update([
            'is_visible' => !$section->is_visible,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_visible' => $section->is_visible,
                'message' => $section->is_visible ? 'Section is now visible' : 'Section is now hidden',
            ]);
        }

        return back()->with('success', $section->is_visible ? 'Section is now visible' : 'Section is now hidden');
    }

    /**
     * Reorder sections (Move Up, Move Down, or bulk order).
     */
    public function reorderSections(Request $request, Website $website): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $website);

        if ($request->has('order') && is_array($request->order)) {
            // Bulk reorder with array of section IDs
            DB::transaction(function () use ($website, $request) {
                foreach ($request->order as $index => $sectionId) {
                    WebsiteSection::where('id', $sectionId)
                        ->where('website_id', $website->id)
                        ->update(['order' => $index]);
                }
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Sections reordered successfully']);
            }
            return back()->with('success', 'Sections reordered successfully.');
        }

        // Single section Move Up / Move Down
        $validated = $request->validate([
            'section_id' => 'required|exists:website_sections,id',
            'direction' => 'required|in:up,down',
        ]);

        $section = WebsiteSection::where('id', $validated['section_id'])
            ->where('website_id', $website->id)
            ->firstOrFail();

        $currentOrder = $section->order;

        if ($validated['direction'] === 'up') {
            $prev = WebsiteSection::where('website_id', $website->id)
                ->where('order', '<', $currentOrder)
                ->orderBy('order', 'desc')
                ->first();

            if ($prev) {
                DB::transaction(function () use ($section, $prev) {
                    $prevOrder = $prev->order;
                    $prev->update(['order' => $section->order]);
                    $section->update(['order' => $prevOrder]);
                });
            }
        } else {
            $next = WebsiteSection::where('website_id', $website->id)
                ->where('order', '>', $currentOrder)
                ->orderBy('order', 'asc')
                ->first();

            if ($next) {
                DB::transaction(function () use ($section, $next) {
                    $nextOrder = $next->order;
                    $next->update(['order' => $section->order]);
                    $section->update(['order' => $nextOrder]);
                });
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Section moved successfully']);
        }

        return redirect()->route('websites.edit', $website)->with('success', 'Section moved.');
    }

    /**
     * Preview Gemini AI Section Regeneration with Side-by-Side Approval Workflow.
     * Does not overwrite database record until owner explicitly accepts.
     */
    public function regenerateSectionPreview(Request $request, WebsiteSection $section): JsonResponse
    {
        $this->authorize('update', $section->website);

        $validated = $request->validate([
            'user_prompt' => 'nullable|string|max:500',
        ]);

        $result = $this->websiteService->generateSectionPreview(
            $section,
            $validated['user_prompt'] ?? ''
        );

        return response()->json($result);
    }

    /**
     * Apply owner-approved section regeneration.
     */
    public function applySectionRegeneration(Request $request, WebsiteSection $section): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $section->website);

        $validated = $request->validate([
            'content' => 'required|array',
        ]);

        $updatedSection = $this->websiteService->applySectionRegeneration(
            $section,
            $validated['content'],
            auth()->id()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Regeneration accepted and applied to section.',
                'section' => $updatedSection,
            ]);
        }

        return redirect()->route('websites.edit', $section->website)
            ->with('success', 'Regenerated content applied successfully.');
    }

    /**
     * Update website design, styling presets, colors, and typography.
     */
    public function updateDesign(Request $request, Website $website): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'design_preset' => 'required|string|in:modern_minimal,premium_elegant,bold_creative',
            'primary_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'font_family' => 'nullable|string|max:255',
            'button_style' => 'nullable|string|in:rounded,pill,sharp',
            'section_spacing' => 'nullable|string|in:compact,normal,spacious',
        ]);

        $preset = WebsiteGenerationService::PRESETS[$validated['design_preset']] ?? WebsiteGenerationService::PRESETS['modern_minimal'];

        $settings = $website->settings ?? [];
        if (!empty($validated['button_style'])) {
            $settings['button_style'] = $validated['button_style'];
        }
        if (!empty($validated['section_spacing'])) {
            $settings['section_spacing'] = $validated['section_spacing'];
        }

        $website->update([
            'title' => !empty($validated['title']) ? trim($validated['title']) : $website->title,
            'design_preset' => $validated['design_preset'],
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'font_family' => !empty($validated['font_family']) ? $validated['font_family'] : $preset['font'],
            'settings' => $settings,
        ]);

        $this->publishingService->createSnapshot(
            $website,
            "Updated theme styling to {$preset['name']}",
            auth()->id()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Design settings updated successfully',
                'website' => $website->fresh(),
            ]);
        }

        return redirect()->route('websites.edit', $website)->with('success', 'Design styling updated successfully.');
    }

    /**
     * Bulk save / autosave all edits in a single atomic transaction.
     */
    public function saveAll(Request $request, Website $website): JsonResponse
    {
        $this->authorize('update', $website);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'primary_color' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:website_sections,id',
            'sections.*.content' => 'required|array',
            'sections.*.is_visible' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($website, $validated) {
            // Update website attributes if provided
            $updates = [];
            if (!empty($validated['title'])) $updates['title'] = $validated['title'];
            if (!empty($validated['primary_color'])) $updates['primary_color'] = $validated['primary_color'];
            if (!empty($validated['secondary_color'])) $updates['secondary_color'] = $validated['secondary_color'];

            if (!empty($updates)) {
                $website->update($updates);
            }

            // Update each section
            foreach ($validated['sections'] as $secData) {
                $section = WebsiteSection::where('id', $secData['id'])
                    ->where('website_id', $website->id)
                    ->first();

                if ($section) {
                    $overrides = $section->design_overrides ?? [];
                    $overrides['is_manually_edited'] = true;
                    $overrides['last_saved_at'] = now()->toIso8601String();

                    $section->update([
                        'content' => $secData['content'],
                        'is_visible' => isset($secData['is_visible']) ? (bool)$secData['is_visible'] : $section->is_visible,
                        'design_overrides' => $overrides,
                    ]);
                }
            }

            // Create immutable version snapshot
            $this->publishingService->createSnapshot(
                $website,
                "Saved custom edits via Website Editor",
                auth()->id()
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'All changes saved successfully.',
            'saved_at' => now()->format('h:i:s A'),
            'current_version' => $website->fresh()->current_version,
        ]);
    }
}
