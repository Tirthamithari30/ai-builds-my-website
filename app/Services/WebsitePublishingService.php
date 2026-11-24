<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\Website;
use App\Models\WebsiteVersion;
use Illuminate\Support\Str;

class WebsitePublishingService
{
    public const RESERVED_SLUGS = [
        'admin',
        'api',
        'dashboard',
        'login',
        'register',
        'logout',
        'businesses',
        'websites',
        'sections',
        'sources',
        'files',
        'facts',
        'sites',
        'settings',
        'profile',
        'home',
        'app',
        'build',
        'storage',
    ];

    public function __construct(
        protected ?FactVerificationService $factService = null
    ) {
        $this->factService = $factService ?? app(FactVerificationService::class);
    }

    /**
     * Pre-flight validation before publishing a website.
     * Enforces that business name is present, visible sections exist, and no unresolved conflicts affect displayed facts.
     */
    public function validateForPublishing(Website $website): array
    {
        $errors = [];
        $business = $website->business;

        // 1. Business name check
        if (empty(trim($business->name ?? ''))) {
            $errors[] = 'Business name is required before publishing.';
        }

        // 2. Visible sections check
        $visibleCount = $website->sections()->where('is_visible', true)->count();
        if ($visibleCount === 0) {
            $errors[] = 'Website must contain at least one visible section to publish.';
        }

        // 3. Factual conflict check
        $factAnalysis = $this->factService->analyzeFacts($business);
        if ($factAnalysis['has_blocking_conflicts']) {
            $errors[] = 'Cannot publish website while unresolved conflicting business facts exist. Please resolve all conflicts in Fact Review first.';
        }

        // 4. Section content validation
        foreach ($website->sections()->where('is_visible', true)->get() as $sec) {
            if (empty($sec->content) || !is_array($sec->content)) {
                $errors[] = "Section '{$sec->section_type}' contains empty content.";
            }
        }

        return [
            'is_valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Validate a custom slug for format, reserved words, and database uniqueness.
     */
    public function validateSlug(string $slug, ?int $ignoreWebsiteId = null): array
    {
        $normalized = Str::slug($slug);

        if (empty($normalized)) {
            return [
                'is_valid' => false,
                'slug' => '',
                'error' => 'Slug cannot be empty and must contain valid letters or numbers.',
            ];
        }

        if (strlen($normalized) < 3 || strlen($normalized) > 80) {
            return [
                'is_valid' => false,
                'slug' => $normalized,
                'error' => 'Slug must be between 3 and 80 characters long.',
            ];
        }

        if (in_array(strtolower($normalized), self::RESERVED_SLUGS, true)) {
            return [
                'is_valid' => false,
                'slug' => $normalized,
                'error' => "The slug '{$normalized}' is reserved by the application.",
            ];
        }

        $query = Website::where('slug', $normalized);
        if ($ignoreWebsiteId) {
            $query->where('id', '!=', $ignoreWebsiteId);
        }

        if ($query->exists()) {
            return [
                'is_valid' => false,
                'slug' => $normalized,
                'error' => "The slug '{$normalized}' is already in use by another website.",
            ];
        }

        return [
            'is_valid' => true,
            'slug' => $normalized,
            'error' => null,
        ];
    }

    /**
     * Update website slug and update active publication URL.
     */
    public function updateSlug(Website $website, string $newSlug, int $userId): Website
    {
        $validation = $this->validateSlug($newSlug, $website->id);
        if (!$validation['is_valid']) {
            throw new \InvalidArgumentException($validation['error']);
        }

        $cleanSlug = $validation['slug'];
        $website->update(['slug' => $cleanSlug]);

        // Update active publication URL if published
        if ($website->is_published) {
            $newUrl = url("/sites/{$cleanSlug}");
            Publication::where('website_id', $website->id)
                ->where('is_active', true)
                ->update(['published_url' => $newUrl]);
        }

        $this->createSnapshot($website, "Updated website slug to '{$cleanSlug}'", $userId);

        return $website;
    }

    /**
     * Generate a URL-safe, unique slug with collision avoidance.
     */
    public function generateUniqueSlug(string $name, ?int $ignoreWebsiteId = null): string
    {
        $baseSlug = Str::slug($name);
        if (empty($baseSlug) || in_array(strtolower($baseSlug), self::RESERVED_SLUGS, true)) {
            $baseSlug = 'business-site';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = Website::where('slug', $slug);
            if ($ignoreWebsiteId) {
                $query->where('id', '!=', $ignoreWebsiteId);
            }

            if (!$query->exists() && !in_array(strtolower($slug), self::RESERVED_SLUGS, true)) {
                return $slug;
            }

            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
    }

    /**
     * Create a complete immutable snapshot version before saving or publishing.
     */
    public function createSnapshot(Website $website, string $changelog, int $userId): WebsiteVersion
    {
        $nextVersionNumber = ($website->versions()->max('version_number') ?? 0) + 1;

        $snapshot = [
            'title' => $website->title,
            'slug' => $website->slug,
            'design_preset' => $website->design_preset,
            'primary_color' => $website->primary_color,
            'secondary_color' => $website->secondary_color,
            'font_family' => $website->font_family,
            'meta_description' => $website->meta_description,
            'settings' => $website->settings,
            'sections' => $website->sections()->get()->map(fn($s) => [
                'section_type' => $s->section_type,
                'order' => $s->order,
                'is_visible' => $s->is_visible,
                'content' => $s->content,
                'design_overrides' => $s->design_overrides,
            ])->all(),
        ];

        $version = WebsiteVersion::create([
            'website_id' => $website->id,
            'version_number' => $nextVersionNumber,
            'snapshot' => $snapshot,
            'changelog' => $changelog,
            'created_by' => $userId,
        ]);

        $website->update(['current_version' => $nextVersionNumber]);

        return $version;
    }

    /**
     * Publish a website live to a public URL.
     */
    public function publish(Website $website, int $userId): Publication
    {
        $validation = $this->validateForPublishing($website);
        if (!$validation['is_valid']) {
            throw new \InvalidArgumentException(implode(' ', $validation['errors']));
        }

        // 1. Create a version snapshot for this publication
        $version = $this->createSnapshot($website, "Published Version {$website->current_version}", $userId);

        // 2. Mark previous publications inactive
        Publication::where('website_id', $website->id)->update(['is_active' => false]);

        // 3. Mark website as published
        $website->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $publicUrl = url("/sites/{$website->slug}");

        // 4. Record new active publication
        return Publication::create([
            'website_id' => $website->id,
            'version_id' => $version->id,
            'published_url' => $publicUrl,
            'published_by' => $userId,
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish a website, taking it offline from anonymous visitors.
     */
    public function unpublish(Website $website): void
    {
        $website->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        Publication::where('website_id', $website->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'unpublished_at' => now(),
            ]);
    }
}
