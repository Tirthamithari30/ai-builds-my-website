<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\Website;
use App\Models\WebsiteVersion;
use Illuminate\Support\Str;

class WebsitePublishingService
{
    /**
     * Generate a URL-safe, unique slug with collision avoidance.
     */
    public function generateUniqueSlug(string $name, ?int $ignoreWebsiteId = null): string
    {
        $baseSlug = Str::slug($name);
        if (empty($baseSlug)) {
            $baseSlug = 'business-site';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = Website::where('slug', $slug);
            if ($ignoreWebsiteId) {
                $query->where('id', '!=', $ignoreWebsiteId);
            }

            if (!$query->exists()) {
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
