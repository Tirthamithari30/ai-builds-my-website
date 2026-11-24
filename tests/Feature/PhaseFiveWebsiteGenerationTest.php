<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Models\WebsiteVersion;
use App\Services\WebsiteGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhaseFiveWebsiteGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Aditi Sharma',
            'email' => 'aditi@sharmaspa.com',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Rahul Verma',
            'email' => 'rahul@vermatech.com',
        ]);

        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Sharma Ayurvedic Spa & Wellness',
            'category' => 'salon',
            'description' => 'Authentic herbal treatments and rejuvenation therapies in Kolhapur.',
            'city' => 'Kolhapur',
            'state' => 'Maharashtra',
            'country' => 'India',
            'address' => '124 Tarabai Park, Kolhapur',
            'phone' => '+91 98220 12345',
            'email' => 'contact@sharmaspa.com',
            'operating_hours' => ['raw' => 'Mon - Sat: 9:00 AM - 8:00 PM'],
            'services' => ['Herbal Massage', 'Abhyanga Therapy', 'Aromatherapy Facials'],
        ]);
    }

    public function test_user_can_view_pre_generation_setup_screen_with_confirmed_facts(): void
    {
        // Add confirmed facts
        ExtractedFact::create([
            'business_id' => $this->business->id,
            'source_type' => 'manual',
            'source_url' => 'Manual Onboarding',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 12345',
            'verification_status' => 'user-confirmed',
            'is_confirmed' => true,
            'user_confirmed_value' => '+91 98220 12345',
            'confidence_score' => 1.0,
            'extraction_time' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('businesses.website.setup', $this->business));

        $response->assertStatus(200);
        $response->assertSee('Generate Website for Sharma Ayurvedic Spa & Wellness');
        $response->assertSee('Pre-Generation Fact Review');
        $response->assertSee('+91 98220 12345');
        $response->assertSee('124 Tarabai Park, Kolhapur');
        $response->assertSee('Modern Minimal');
        $response->assertSee('Premium Elegant');
        $response->assertSee('Bold Creative');
    }

    public function test_pre_generation_setup_identifies_missing_optional_information(): void
    {
        $response = $this->actingAs($this->user)->get(route('businesses.website.setup', $this->business));

        $response->assertStatus(200);
        $response->assertSee('Missing Information Handled Accurately');
    }

    public function test_blocking_conflicts_prevent_website_generation(): void
    {
        // Create conflicting facts for phone number
        ExtractedFact::create([
            'business_id' => $this->business->id,
            'source_type' => 'webpage',
            'source_url' => 'https://example.com/source1',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 11111',
            'verification_status' => 'conflicting',
            'confidence_score' => 0.8,
            'extraction_time' => now(),
        ]);

        ExtractedFact::create([
            'business_id' => $this->business->id,
            'source_type' => 'document',
            'source_url' => 'brochure.pdf',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 22222',
            'verification_status' => 'conflicting',
            'confidence_score' => 0.9,
            'extraction_time' => now(),
        ]);

        // 1. Setup view must warn user
        $setupResponse = $this->actingAs($this->user)->get(route('businesses.website.setup', $this->business));
        $setupResponse->assertStatus(200);
        $setupResponse->assertSee('Unresolved Conflicting Information Detected');
        $setupResponse->assertSee('Resolve Conflicts in Fact Review');

        // 2. Generate post must be blocked and redirected with error
        $generateResponse = $this->actingAs($this->user)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Sharma Spa Website',
            'website_type' => 'business',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'services', 'contact', 'footer'],
        ]);

        $generateResponse->assertRedirect(route('businesses.facts.index', $this->business));
        $generateResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('websites', ['business_id' => $this->business->id]);
    }

    public function test_user_can_generate_website_with_fallback_when_gemini_unconfigured(): void
    {
        $response = $this->actingAs($this->user)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Sharma Ayurvedic Spa Live',
            'website_type' => 'service_provider',
            'design_preset' => 'premium_elegant',
            'primary_color' => '#b45309',
            'secondary_color' => '#1c1917',
            'sections' => ['hero', 'about', 'services', 'location', 'contact', 'footer'],
        ]);

        $this->assertDatabaseHas('websites', [
            'business_id' => $this->business->id,
            'title' => 'Sharma Ayurvedic Spa Live',
            'design_preset' => 'premium_elegant',
            'primary_color' => '#b45309',
            'secondary_color' => '#1c1917',
        ]);

        $website = Website::where('business_id', $this->business->id)->first();
        $this->assertNotNull($website);
        $this->assertGreaterThanOrEqual(5, $website->sections()->count());

        // Snapshot created in website_versions
        $this->assertDatabaseHas('website_versions', [
            'website_id' => $website->id,
            'version_number' => 1,
        ]);

        $response->assertRedirect(route('websites.preview', $website));
        $response->assertSessionHas('success');
    }

    public function test_user_can_generate_website_with_gemini_ai_mocked_response(): void
    {
        config(['services.gemini.api_key' => 'mock-gemini-key']);

        $mockPayload = [
            'website_title' => 'Sharma Ayurvedic Wellness Haven',
            'tagline' => 'Ancient healing for contemporary well-being in Kolhapur',
            'meta_description' => 'Experience authentic Ayurvedic therapies and rejuvenation in Kolhapur.',
            'sections' => [
                [
                    'section_type' => 'hero',
                    'title' => 'Hero Banner',
                    'is_visible' => true,
                    'fact_references' => ['phone', 'address'],
                    'content' => [
                        'badge' => 'Verified Ayurvedic Sanctuary in Kolhapur',
                        'heading' => 'Restore Balance & Vitality',
                        'subheading' => 'Authentic herbal treatments and rejuvenation therapies in Kolhapur.',
                        'primary_button_text' => 'Book Consultation',
                        'primary_button_link' => '#contact',
                        'hero_image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef',
                    ],
                ],
                [
                    'section_type' => 'about',
                    'title' => 'About Us',
                    'is_visible' => true,
                    'fact_references' => ['description'],
                    'content' => [
                        'badge' => 'Our Tradition',
                        'heading' => 'Rooted in Authentic Ayurveda',
                        'description_p1' => 'Dedicated to timeless herbal care in Kolhapur.',
                        'highlights' => [
                            ['title' => 'Certified Therapies', 'desc' => 'Grounded in authentic wellness.'],
                        ],
                    ],
                ],
                [
                    'section_type' => 'contact',
                    'title' => 'Contact Us',
                    'is_visible' => true,
                    'fact_references' => ['phone', 'email', 'address'],
                    'content' => [
                        'badge' => 'Reach Us',
                        'heading' => 'Connect With Our Specialists',
                        'phone' => '+91 98220 12345',
                        'email' => 'contact@sharmaspa.com',
                        'address' => '124 Tarabai Park, Kolhapur',
                        'form_title' => 'Book an Appointment',
                    ],
                ],
                [
                    'section_type' => 'footer',
                    'title' => 'Footer',
                    'is_visible' => true,
                    'fact_references' => ['phone', 'email'],
                    'content' => [
                        'copyright' => '© 2026 Sharma Ayurvedic Spa. All rights reserved.',
                        'tagline' => 'Holistic Wellness in Kolhapur',
                        'phone' => '+91 98220 12345',
                        'email' => 'contact@sharmaspa.com',
                    ],
                ],
            ],
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($mockPayload)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Sharma Ayurvedic Wellness Haven',
            'website_type' => 'business',
            'design_preset' => 'bold_creative',
            'primary_color' => '#7c3aed',
            'secondary_color' => '#18181b',
            'sections' => ['hero', 'about', 'contact', 'footer'],
        ]);

        $website = Website::where('business_id', $this->business->id)->first();
        $this->assertNotNull($website);
        $this->assertTrue($website->settings['ai_generated'] ?? false);
        $this->assertEquals('gemini_ai', $website->settings['generation_mode'] ?? '');
        $this->assertEquals(4, $website->sections()->count());

        $response->assertRedirect(route('websites.preview', $website));
    }

    public function test_invalid_ai_response_falls_back_gracefully_to_fact_grounded_generation(): void
    {
        config(['services.gemini.api_key' => 'mock-gemini-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Rate limit exceeded or internal model error'],
            ], 500),
        ]);

        $response = $this->actingAs($this->user)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Fallback Generated Spa Site',
            'website_type' => 'business',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'services', 'contact', 'footer'],
        ]);

        $website = Website::where('business_id', $this->business->id)->first();
        $this->assertNotNull($website);
        // Did not crash! Fell back to deterministic generator
        $this->assertFalse($website->settings['ai_generated'] ?? true);
        $this->assertEquals('deterministic_grounded_fallback', $website->settings['generation_mode'] ?? '');
        $this->assertGreaterThanOrEqual(4, $website->sections()->count());

        $response->assertRedirect(route('websites.preview', $website));
    }

    public function test_generated_website_only_uses_confirmed_facts_and_avoids_hallucination(): void
    {
        $response = $this->actingAs($this->user)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Sharma Spa Authenticity Check',
            'website_type' => 'business',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['contact', 'location', 'footer'],
        ]);

        $website = Website::where('business_id', $this->business->id)->first();
        $contactSec = $website->sections()->where('section_type', 'contact')->first();

        $this->assertNotNull($contactSec);
        $this->assertEquals('+91 98220 12345', $contactSec->content['phone']);
        $this->assertEquals('contact@sharmaspa.com', $contactSec->content['email']);
        $this->assertEquals('124 Tarabai Park, Kolhapur', $contactSec->content['address']);
    }

    public function test_user_can_preview_website_in_desktop_tablet_and_mobile_views(): void
    {
        $service = app(WebsiteGenerationService::class);
        $website = $service->generateWebsite($this->business);

        // 1. Preview screen
        $previewResponse = $this->actingAs($this->user)->get(route('websites.preview', $website));
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('Desktop');
        $previewResponse->assertSee('Tablet (768px)');
        $previewResponse->assertSee('Mobile (375px)');
        $previewResponse->assertSee(route('websites.preview.frame', $website));

        // 2. Preview frame (standalone HTML)
        $frameResponse = $this->actingAs($this->user)->get(route('websites.preview.frame', $website));
        $frameResponse->assertStatus(200);
        $frameResponse->assertSee($this->business->name);
        $frameResponse->assertSee($this->business->phone);
    }

    public function test_user_cannot_access_or_generate_for_another_users_business(): void
    {
        $service = app(WebsiteGenerationService::class);
        $website = $service->generateWebsite($this->business);

        // User B cannot access User A's setup screen
        $setupResponse = $this->actingAs($this->otherUser)->get(route('businesses.website.setup', $this->business));
        $setupResponse->assertStatus(403);

        // User B cannot generate for User A's business
        $generateResponse = $this->actingAs($this->otherUser)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Malicious Hack Attempt',
            'website_type' => 'business',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'contact'],
        ]);
        $generateResponse->assertStatus(403);

        // User B cannot preview User A's website
        $previewResponse = $this->actingAs($this->otherUser)->get(route('websites.preview', $website));
        $previewResponse->assertStatus(403);

        $frameResponse = $this->actingAs($this->otherUser)->get(route('websites.preview.frame', $website));
        $frameResponse->assertStatus(403);
    }

    public function test_user_can_restore_previous_version_snapshot(): void
    {
        $service = app(WebsiteGenerationService::class);

        // Version 1
        $website = $service->generateWebsite($this->business, [
            'title' => 'First Generation Title',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'contact'],
        ]);

        $v1 = WebsiteVersion::where('website_id', $website->id)->where('version_number', 1)->first();
        $this->assertNotNull($v1);

        // Version 2 (Regeneration)
        $website = $service->generateWebsite($this->business, [
            'title' => 'Second Generation Title',
            'design_preset' => 'premium_elegant',
            'primary_color' => '#b45309',
            'secondary_color' => '#1c1917',
            'sections' => ['hero', 'about', 'services', 'contact', 'footer'],
        ]);

        $this->assertEquals(2, $website->fresh()->current_version);
        $this->assertEquals('Second Generation Title', $website->fresh()->title);

        // Restore Version 1
        $restoreResponse = $this->actingAs($this->user)->post(route('websites.versions.restore', [$website, $v1]));

        $restoreResponse->assertRedirect(route('websites.preview', $website));
        $restoreResponse->assertSessionHas('success');

        $refreshed = $website->fresh();
        $this->assertEquals('First Generation Title', $refreshed->title);
        $this->assertEquals('modern_minimal', $refreshed->design_preset);
        $this->assertEquals(3, $refreshed->sections()->count());
    }

    public function test_custom_color_and_section_selection_validation(): void
    {
        // Invalid color regex
        $response = $this->actingAs($this->user)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Invalid Site',
            'website_type' => 'business',
            'design_preset' => 'modern_minimal',
            'primary_color' => 'not-a-hex-color',
            'secondary_color' => '#0f172a',
            'sections' => ['hero'],
        ]);

        $response->assertSessionHasErrors(['primary_color']);

        // Empty sections array
        $emptySecResponse = $this->actingAs($this->user)->post(route('businesses.website.generate', $this->business), [
            'title' => 'Invalid Site',
            'website_type' => 'business',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => [],
        ]);

        $emptySecResponse->assertSessionHasErrors(['sections']);
    }
}
