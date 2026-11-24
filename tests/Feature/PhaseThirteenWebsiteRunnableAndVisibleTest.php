<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\FactConfirmation;
use App\Models\Publication;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PhaseThirteenWebsiteRunnableAndVisibleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Business $business;
    protected Website $website;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Demo Business Owner',
            'email' => 'owner@runnable-demo.com',
        ]);

        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Patil Traditional Spices',
            'category' => 'food_beverage',
            'city' => 'Kolhapur',
            'state' => 'Maharashtra',
            'country' => 'India',
            'phone' => '+91 94220 12345',
            'email' => 'patil@spicesdemo.com',
            'description' => 'Authentic Kolhapuri masala and hand-ground spices crafted since 1985.',
            'products' => ['Kolhapuri Kanda Lasun Masala', 'Special Garam Masala', 'Pure Turmeric Powder'],
            'services' => ['Custom Spice Blending', 'Bulk Wholesale Supply', 'Gift Hamper Packing'],
            'operating_hours' => ['monday_to_saturday' => '9:00 AM - 8:00 PM', 'sunday' => 'Closed'],
        ]);

        FactConfirmation::create([
            'business_id' => $this->business->id,
            'fact_key' => 'business_name',
            'confirmed_value' => 'Patil Traditional Spices',
            'resolution_type' => 'accepted',
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now(),
        ]);

        FactConfirmation::create([
            'business_id' => $this->business->id,
            'fact_key' => 'phone',
            'confirmed_value' => '+91 94220 12345',
            'resolution_type' => 'accepted',
            'confirmed_by' => $this->user->id,
            'confirmed_at' => now(),
        ]);

        $this->website = Website::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'title' => 'Patil Traditional Spices Official',
            'slug' => 'patil-spices-runnable',
            'design_preset' => 'warm_artisan',
            'primary_color' => '#d97706',
            'secondary_color' => '#78350f',
            'font_family' => 'Playfair Display',
            'is_published' => true,
            'published_at' => now(),
            'settings' => [
                'seo_title' => 'Patil Spices | Authentic Kolhapuri Masalas',
                'seo_description' => 'Experience genuine stone-ground spices crafted in Kolhapur.',
            ],
        ]);

        WebsiteSection::create([
            'website_id' => $this->website->id,
            'section_type' => 'hero',
            'title' => 'Authentic Kolhapuri Spices Since 1985',
            'subtitle' => 'Handcrafted stone-ground masalas preserving regional heritage.',
            'content' => [
                'heading' => 'Authentic Kolhapuri Spices Since 1985',
                'subheading' => 'Handcrafted stone-ground masalas preserving regional heritage.',
                'primary_button_text' => 'Order Now',
                'primary_button_link' => '#contact',
            ],
            'order' => 1,
            'is_visible' => true,
        ]);

        WebsiteSection::create([
            'website_id' => $this->website->id,
            'section_type' => 'contact',
            'title' => 'Get in Touch',
            'subtitle' => 'Visit our store in Kolhapur or call us today.',
            'content' => [
                'heading' => 'Get in Touch',
                'subheading' => 'Visit our store in Kolhapur or call us today.',
                'phone' => '+91 94220 12345',
                'email' => 'patil@spicesdemo.com',
                'address' => 'Kolhapur, Maharashtra, India',
            ],
            'order' => 2,
            'is_visible' => true,
        ]);

        Publication::create([
            'website_id' => $this->website->id,
            'published_url' => url('/sites/patil-spices-runnable'),
            'published_by' => $this->user->id,
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * 1. Test Landing Page accessibility and elements.
     */
    public function test_landing_page_is_accessible_and_renders_cleanly(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('AI Builds My Website');
        $response->assertSee('Get Started');
        $response->assertSee('Sign In');
        $response->assertSee('Full-Stack AI Website Generator Active');
    }

    /**
     * 2. Test Registration Page accessibility.
     */
    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('AI Builds My Website');
        $response->assertSee('Create your account');
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    /**
     * 3. Test Login Page accessibility.
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in to your account');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    /**
     * 4. Test User Dashboard accessibility.
     */
    public function test_user_dashboard_is_accessible_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Demo Business Owner');
        $response->assertSee('Saved Projects');
        $response->assertSee('Patil Traditional Spices');
        $response->assertSee('Create New Website');
    }

    /**
     * 5. Test Website Generation Setup Page accessibility.
     */
    public function test_website_generation_setup_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get("/businesses/{$this->business->id}/website/setup");

        $response->assertStatus(200);
        $response->assertSee('Generate Website for Patil Traditional Spices');
        $response->assertSee('Patil Traditional Spices');
        $response->assertSee('Generate Website with Gemini AI');
    }

    /**
     * 6. Test Website Preview Page accessibility.
     */
    public function test_website_preview_and_preview_frame_are_accessible(): void
    {
        // 6a. Responsive frame container
        $containerResponse = $this->actingAs($this->user)->get("/websites/{$this->website->id}/preview");
        $containerResponse->assertStatus(200);
        $containerResponse->assertSee('Website Preview');
        $containerResponse->assertSee('Website Preview Frame');
        $containerResponse->assertSee('Desktop');
        $containerResponse->assertSee('Tablet');
        $containerResponse->assertSee('Mobile');

        // 6b. Inner preview frame content
        $frameResponse = $this->actingAs($this->user)->get("/websites/{$this->website->id}/preview-frame");
        $frameResponse->assertStatus(200);
        $frameResponse->assertSee('Authentic Kolhapuri Spices Since 1985');
        $frameResponse->assertSee('+91 94220 12345');
    }

    /**
     * 7. Test Website Management / Business Show Page accessibility.
     */
    public function test_business_management_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get("/businesses/{$this->business->id}");

        $response->assertStatus(200);
        $response->assertSee('Patil Traditional Spices');
        $response->assertSee('Kolhapur');
        $response->assertSee('Documents');
        $response->assertSee('Sources');
    }

    /**
     * Bonus Verification: Public Live Website is accessible without authentication.
     */
    public function test_public_website_is_accessible_without_authentication(): void
    {
        $response = $this->get('/sites/patil-spices-runnable');

        $response->assertStatus(200);
        $response->assertSee('Authentic Kolhapuri Spices Since 1985');
        $response->assertSee('Patil Traditional Spices');
        $response->assertSee('+91 94220 12345');
        $response->assertSee('Patil Spices | Authentic Kolhapuri Masalas');
    }

    /**
     * Frontend Build Assets Verification: manifest.json contains compiled CSS and JS.
     */
    public function test_frontend_build_assets_exist_and_manifest_is_valid(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(File::get($manifestPath), true);
        $this->assertArrayHasKey('resources/css/app.css', $manifest);
        $this->assertArrayHasKey('resources/js/app.js', $manifest);

        $cssFile = public_path('build/' . $manifest['resources/css/app.css']['file']);
        $jsFile = public_path('build/' . $manifest['resources/js/app.js']['file']);

        $this->assertFileExists($cssFile, 'Compiled CSS bundle must exist in public/build');
        $this->assertFileExists($jsFile, 'Compiled JS bundle must exist in public/build');
    }
}
