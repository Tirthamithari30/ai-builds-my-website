<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use App\Models\Publication;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Models\WebsiteVersion;
use App\Services\AiService;
use App\Services\FactVerificationService;
use App\Services\SourceExtractionService;
use App\Services\WebResearchService;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneArchitectureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the landing page renders with correct Phase 1 branding and HTTP 200.
     */
    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('AI Builds My Website');
        $response->assertSee('Your Business. Your Information. Your Website.');
        $response->assertSee('Phase 1');
    }

    /**
     * Test database models, relationships, and persistence.
     */
    public function test_all_domain_models_persist_correctly(): void
    {
        // 1. User
        $user = User::factory()->create([
            'name' => 'Demo Business Owner',
            'email' => 'owner@example.com',
        ]);

        // 2. Business
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Urban Brew Cafe',
            'category' => 'cafe',
            'city' => 'Kolhapur',
            'state' => 'Maharashtra',
            'phone' => '+91 98220 11223',
            'email' => 'hello@urbanbrew.com',
            'description' => 'Artisanal roastery and cafe serving specialty coffee in Kolhapur.',
            'operating_hours' => ['Mon-Sat' => '8:00 AM - 10:00 PM'],
        ]);

        $this->assertDatabaseHas('businesses', ['name' => 'Urban Brew Cafe']);

        // 3. Business Source
        $source = BusinessSource::create([
            'business_id' => $business->id,
            'source_type' => 'google_business',
            'url' => 'https://maps.google.com/?cid=123456789',
            'title' => 'Urban Brew Cafe - Google Maps',
            'status' => 'crawled',
        ]);

        $this->assertDatabaseHas('business_sources', ['title' => 'Urban Brew Cafe - Google Maps']);

        // 4. Uploaded File
        $file = UploadedFile::create([
            'business_id' => $business->id,
            'original_name' => 'menu_summer.pdf',
            'file_path' => 'uploads/menu_summer.pdf',
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 102400,
            'processing_status' => 'completed',
        ]);

        $this->assertDatabaseHas('uploaded_files', ['original_name' => 'menu_summer.pdf']);

        // 5. Extracted Facts
        $fact1 = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'webpage',
            'source_id' => $source->id,
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 11223',
            'verification_status' => 'extracted',
        ]);

        $this->assertDatabaseHas('extracted_facts', ['fact_value' => '+91 98220 11223']);

        // 6. Fact Confirmation
        $confirmation = FactConfirmation::create([
            'business_id' => $business->id,
            'fact_key' => 'phone',
            'confirmed_value' => '+91 98220 11223',
            'resolution_type' => 'accepted',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
        ]);

        $this->assertDatabaseHas('fact_confirmations', ['confirmed_value' => '+91 98220 11223']);

        // 7. Website
        $website = Website::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'title' => 'Urban Brew Cafe',
            'slug' => 'urban-brew-cafe',
            'design_preset' => 'modern_minimal',
            'is_published' => false,
        ]);

        $this->assertDatabaseHas('websites', ['slug' => 'urban-brew-cafe']);

        // 8. Website Section
        $section = WebsiteSection::create([
            'website_id' => $website->id,
            'section_type' => 'hero',
            'order' => 0,
            'is_visible' => true,
            'content' => ['heading' => 'Welcome to Urban Brew Cafe'],
        ]);

        $this->assertDatabaseHas('website_sections', ['section_type' => 'hero']);

        // 9. Website Version
        $version = WebsiteVersion::create([
            'website_id' => $website->id,
            'version_number' => 1,
            'snapshot' => ['title' => 'Urban Brew Cafe', 'sections' => []],
            'changelog' => 'Initial Version',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('website_versions', ['version_number' => 1]);

        // 10. Publication
        $publication = Publication::create([
            'website_id' => $website->id,
            'version_id' => $version->id,
            'published_url' => url('/sites/urban-brew-cafe'),
            'published_by' => $user->id,
            'is_active' => true,
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('publications', ['published_url' => url('/sites/urban-brew-cafe')]);
    }

    /**
     * Test SSRF Protection in WebResearchService.
     */
    public function test_ssrf_protection_blocks_malicious_and_internal_targets(): void
    {
        $service = app(WebResearchService::class);

        // Disallowed schemes
        $this->assertFalse($service->validateSafeUrl('ftp://example.com')['safe']);
        $this->assertFalse($service->validateSafeUrl('file:///etc/passwd')['safe']);

        // Localhost and loopback
        $this->assertFalse($service->validateSafeUrl('http://localhost:8000')['safe']);
        $this->assertFalse($service->validateSafeUrl('http://127.0.0.1:3306')['safe']);
        $this->assertFalse($service->validateSafeUrl('http://0.0.0.0')['safe']);

        // Cloud metadata endpoints
        $this->assertFalse($service->validateSafeUrl('http://169.254.169.254/latest/meta-data/')['safe']);

        // Valid external public URL
        $this->assertTrue($service->validateSafeUrl('https://websites.co.in')['safe']);
    }

    /**
     * Test Conflict Detection in FactVerificationService.
     */
    public function test_fact_verification_detects_conflicting_information(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Elite Salon',
            'category' => 'salon',
            'city' => 'Pune',
        ]);

        // Source 1 provides Phone A
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'webpage',
            'source_url' => 'https://elitesalon.com',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98000 11111',
            'verification_status' => 'extracted',
        ]);

        // Source 2 provides Phone B (Conflict!)
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'google_business',
            'source_url' => 'https://maps.google.com/elitesalon',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98000 22222',
            'verification_status' => 'extracted',
        ]);

        $verificationService = app(FactVerificationService::class);
        $analysis = $verificationService->analyzeFacts($business);

        $this->assertTrue($analysis['has_blocking_conflicts']);
        $this->assertArrayHasKey('phone', $analysis['conflicts']);
        $this->assertCount(2, $analysis['conflicts']['phone']['candidates']);

        // Now resolve conflict with user confirmation
        $verificationService->resolveConflict($business, 'phone', '+91 98000 11111', $user->id);

        $freshAnalysis = $verificationService->analyzeFacts($business);
        $this->assertFalse($freshAnalysis['has_blocking_conflicts']);
        $this->assertEquals('+91 98000 11111', $freshAnalysis['verified_facts']['phone']['fact_value']);
        $this->assertEquals('user-confirmed', $freshAnalysis['verified_facts']['phone']['status']);
    }

    /**
     * Test Slug Generation and Collision Avoidance in WebsitePublishingService.
     */
    public function test_publishing_service_handles_slug_collisions(): void
    {
        $service = app(WebsitePublishingService::class);
        $user = User::factory()->create();

        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Urban Cafe',
            'category' => 'cafe',
        ]);

        $slug1 = $service->generateUniqueSlug('Urban Cafe');
        $this->assertEquals('urban-cafe', $slug1);

        // Create website with that slug
        Website::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'title' => 'Urban Cafe',
            'slug' => $slug1,
        ]);

        // Second business with same name should resolve collision cleanly
        $slug2 = $service->generateUniqueSlug('Urban Cafe');
        $this->assertEquals('urban-cafe-2', $slug2);
    }

    /**
     * Test that WebsiteGenerationService determines appropriate sections by category.
     */
    public function test_website_generation_tailors_sections_to_category(): void
    {
        $service = app(WebsiteGenerationService::class);

        $restaurantSections = $service->determineSectionsForCategory('restaurant');
        $this->assertContains('products', $restaurantSections);
        $this->assertContains('gallery', $restaurantSections);
        $this->assertContains('location', $restaurantSections);

        $consultantSections = $service->determineSectionsForCategory('consultant');
        $this->assertContains('services', $consultantSections);
        $this->assertContains('faq', $consultantSections);
    }
}
