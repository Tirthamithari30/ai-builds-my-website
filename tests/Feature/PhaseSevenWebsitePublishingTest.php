<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\ExtractedFact;
use App\Models\Publication;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Models\WebsiteVersion;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSevenWebsitePublishingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Business $business;
    protected Website $website;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Vikram Patil',
            'email' => 'vikram@kolhapurfood.com',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Sneha Kulkarni',
            'email' => 'sneha@kulkarnilaw.com',
        ]);

        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Patil Traditional Spices & Foods',
            'category' => 'retail',
            'description' => 'Authentic Kolhapuri masalas and artisanal spice blends crafted since 1985.',
            'city' => 'Kolhapur',
            'state' => 'Maharashtra',
            'country' => 'India',
            'address' => '45 Shahupuri 2nd Lane, Kolhapur',
            'phone' => '+91 94220 54321',
            'email' => 'orders@patilspices.com',
            'operating_hours' => ['raw' => 'Mon - Sat: 10:00 AM - 9:00 PM'],
            'products' => ['Kolhapuri Kanda Lasun Masala', 'Special Mutton Masala', 'Goda Masala'],
        ]);

        // Generate a grounded website using Phase 5 generation engine
        $service = app(WebsiteGenerationService::class);
        $this->website = $service->generateWebsite($this->business, [
            'title' => 'Patil Traditional Spices Official',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'products', 'contact', 'footer'],
        ]);
    }

    public function test_owner_can_publish_website_and_creates_active_publication(): void
    {
        $response = $this->actingAs($this->user)->post(route('websites.publish', $this->website));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->website->refresh();
        $this->assertTrue($this->website->is_published);
        $this->assertNotNull($this->website->published_at);

        // Verify publication record created
        $publication = Publication::where('website_id', $this->website->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($publication);
        $this->assertEquals(url("/sites/{$this->website->slug}"), $publication->published_url);
        $this->assertEquals($this->user->id, $publication->published_by);

        // Verify snapshot version created
        $this->assertGreaterThan(1, $this->website->versions()->count());
    }

    public function test_anonymous_visitor_can_access_published_website_with_full_seo(): void
    {
        // First publish the website
        app(WebsitePublishingService::class)->publish($this->website, $this->user->id);
        $this->website->refresh();

        // Anonymous public visitor request
        $response = $this->get('/sites/' . $this->website->slug);

        $response->assertStatus(200);
        $response->assertSee('Patil Traditional Spices');
        $response->assertSee('Kolhapuri');
        $response->assertSee('+91 94220 54321');

        // Verify SEO & Robots tags
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertSee('<link rel="canonical" href="' . url('/sites/' . $this->website->slug) . '">', false);
        $response->assertSee('<meta property="og:url" content="' . url('/sites/' . $this->website->slug) . '">', false);
        $response->assertSee('<meta property="og:site_name" content="Patil Traditional Spices &amp; Foods">', false);
    }

    public function test_anonymous_visitor_strictly_gets_404_for_unpublished_draft_website(): void
    {
        $this->assertFalse($this->website->is_published);

        // Accessing draft via public URL returns 404 Not Found
        $response = $this->get('/sites/' . $this->website->slug);
        $response->assertStatus(404);
    }

    public function test_anonymous_visitor_gets_404_for_non_existent_slug(): void
    {
        $response = $this->get('/sites/completely-fake-unknown-slug-xyz');
        $response->assertStatus(404);
    }

    public function test_owner_can_unpublish_website_taking_it_offline_immediately(): void
    {
        // Publish
        app(WebsitePublishingService::class)->publish($this->website, $this->user->id);
        $this->website->refresh();

        $publicUrl = '/sites/' . $this->website->slug;
        $this->get($publicUrl)->assertStatus(200);

        // Owner unpublishes
        $response = $this->actingAs($this->user)->post(route('websites.unpublish', $this->website));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->website->refresh();
        $this->assertFalse($this->website->is_published);
        $this->assertNull($this->website->published_at);

        // Publication record is deactivated
        $activeCount = Publication::where('website_id', $this->website->id)->where('is_active', true)->count();
        $this->assertEquals(0, $activeCount);

        // Anonymous visitor now gets 404
        $this->get($publicUrl)->assertStatus(404);
    }

    public function test_republishing_preserves_version_history_and_keeps_single_active_publication(): void
    {
        $service = app(WebsitePublishingService::class);

        // Publish v1
        $service->publish($this->website, $this->user->id);
        $this->website->refresh();
        $this->assertTrue($this->website->is_published);

        // Unpublish
        $service->unpublish($this->website);
        $this->website->refresh();
        $this->assertFalse($this->website->is_published);

        // Publish v2
        $service->publish($this->website, $this->user->id);
        $this->website->refresh();
        $this->assertTrue($this->website->is_published);

        // Exactly 1 active publication
        $activePubs = Publication::where('website_id', $this->website->id)->where('is_active', true)->get();
        $this->assertCount(1, $activePubs);

        // Total publication records = 2 (history preserved)
        $totalPubs = Publication::where('website_id', $this->website->id)->count();
        $this->assertEquals(2, $totalPubs);
    }

    public function test_pre_flight_publishing_validation_blocks_if_zero_visible_sections(): void
    {
        // Hide all sections
        $this->website->sections()->update(['is_visible' => false]);

        $response = $this->actingAs($this->user)->post(route('websites.publish', $this->website));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->website->refresh();
        $this->assertFalse($this->website->is_published);
    }

    public function test_pre_flight_publishing_validation_blocks_if_unresolved_conflicts_exist(): void
    {
        // Create competing extracted facts leading to an unresolved conflict
        ExtractedFact::create([
            'business_id' => $this->business->id,
            'source_type' => 'document',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 94220 54321',
            'verification_status' => 'extracted',
        ]);
        ExtractedFact::create([
            'business_id' => $this->business->id,
            'source_type' => 'webpage',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 99999 88888',
            'verification_status' => 'extracted',
        ]);

        $response = $this->actingAs($this->user)->post(route('websites.publish', $this->website));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->website->refresh();
        $this->assertFalse($this->website->is_published);
    }

    public function test_owner_can_update_vanity_slug_with_collision_avoidance(): void
    {
        // Publish first
        app(WebsitePublishingService::class)->publish($this->website, $this->user->id);
        $this->website->refresh();

        $newSlug = 'patil-masala-store';

        $response = $this->actingAs($this->user)->put(route('websites.slug.update', $this->website), [
            'slug' => $newSlug,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->website->refresh();
        $this->assertEquals($newSlug, $this->website->slug);

        // Publication URL is updated
        $pub = Publication::where('website_id', $this->website->id)->where('is_active', true)->first();
        $this->assertEquals(url("/sites/{$newSlug}"), $pub->published_url);

        // New URL works for anonymous visitor
        $this->get("/sites/{$newSlug}")->assertStatus(200);

        // Old slug returns 404
        $this->get('/sites/patil-traditional-spices-official')->assertStatus(404);
    }

    public function test_custom_slug_validation_rejects_reserved_slugs(): void
    {
        $response = $this->actingAs($this->user)->put(route('websites.slug.update', $this->website), [
            'slug' => 'admin',
        ]);

        $response->assertSessionHasErrors('slug');

        $this->website->refresh();
        $this->assertNotEquals('admin', $this->website->slug);
    }

    public function test_custom_slug_validation_rejects_duplicate_slug_from_another_website(): void
    {
        // Create another website with slug 'kolhapur-spices'
        Website::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'title' => 'Another Spices Website',
            'slug' => 'kolhapur-spices',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'meta_description' => 'Test website',
        ]);

        // Attempt to change our website's slug to 'kolhapur-spices'
        $response = $this->actingAs($this->user)->put(route('websites.slug.update', $this->website), [
            'slug' => 'kolhapur-spices',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_owner_can_update_seo_and_social_metadata(): void
    {
        // Publish website
        app(WebsitePublishingService::class)->publish($this->website, $this->user->id);
        $this->website->refresh();

        $response = $this->actingAs($this->user)->put(route('websites.seo.update', $this->website), [
            'seo_title' => 'Patil Spices — Best Kolhapuri Masalas in Maharashtra',
            'meta_description' => 'Buy pure authentic Kolhapuri masalas directly from Patil Traditional Spices & Foods.',
            'og_image' => 'https://example.com/spices-banner.jpg',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->website->refresh();
        $this->assertEquals('Patil Spices — Best Kolhapuri Masalas in Maharashtra', $this->website->settings['seo_title']);
        $this->assertEquals('Buy pure authentic Kolhapuri masalas directly from Patil Traditional Spices & Foods.', $this->website->meta_description);

        // Verify rendered tags on public website
        $publicResponse = $this->get('/sites/' . $this->website->slug);
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('<title>Patil Spices — Best Kolhapuri Masalas in Maharashtra</title>', false);
        $publicResponse->assertSee('<meta name="description" content="Buy pure authentic Kolhapuri masalas directly from Patil Traditional Spices &amp; Foods.">', false);
        $publicResponse->assertSee('<meta property="og:image" content="https://example.com/spices-banner.jpg">', false);
    }

    public function test_unauthorized_user_cannot_publish_or_modify_another_users_website(): void
    {
        // User B tries to publish User A's website
        $responsePublish = $this->actingAs($this->otherUser)->post(route('websites.publish', $this->website));
        $responsePublish->assertStatus(403);

        // User B tries to unpublish User A's website
        $responseUnpublish = $this->actingAs($this->otherUser)->post(route('websites.unpublish', $this->website));
        $responseUnpublish->assertStatus(403);

        // User B tries to update User A's slug
        $responseSlug = $this->actingAs($this->otherUser)->put(route('websites.slug.update', $this->website), [
            'slug' => 'hacked-slug',
        ]);
        $responseSlug->assertStatus(403);

        // User B tries to update User A's SEO
        $responseSeo = $this->actingAs($this->otherUser)->put(route('websites.seo.update', $this->website), [
            'meta_description' => 'Hacked meta description',
        ]);
        $responseSeo->assertStatus(403);
    }

    public function test_dashboard_and_preview_display_published_status_and_links(): void
    {
        // Publish website
        app(WebsitePublishingService::class)->publish($this->website, $this->user->id);
        $this->website->refresh();

        // Dashboard view shows Published badge and public link
        $dashResponse = $this->actingAs($this->user)->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Published');
        $dashResponse->assertSee('/sites/' . $this->website->slug);

        // Preview view shows Published Live badge and Unpublish button
        $prevResponse = $this->actingAs($this->user)->get(route('websites.preview', $this->website));
        $prevResponse->assertStatus(200);
        $prevResponse->assertSee('Published Live');
        $prevResponse->assertSee('/sites/' . $this->website->slug);
        $prevResponse->assertSee('Unpublish');
    }
}
