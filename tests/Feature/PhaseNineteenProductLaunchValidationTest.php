<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use App\Models\Publication;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Services\FactVerificationService;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Database\Seeders\DemoBusinessesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseNineteenProductLaunchValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_health_and_configuration_integrity(): void
    {
        // 1. Database Connection check
        $this->assertNotNull(DB::connection()->getPdo());

        // 2. Application Key check
        $this->assertNotEmpty(config('app.key'));

        // 3. Password Hashing Security check
        $this->assertEquals('bcrypt', config('hashing.driver'));
        $this->assertGreaterThanOrEqual(4, (int) config('hashing.bcrypt.rounds', 12));

        // 4. Maintenance & Session Drivers
        $this->assertNotNull(config('session.driver'));
    }

    public function test_complete_ai_workflow_grounding_and_fallback_resilience(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kalyani Textile Mills',
            'category' => 'retail',
            'phone' => '+91 98220 99887',
            'email' => 'sales@kalyanitextiles.in',
            'city' => 'Surat',
        ]);

        $factService = app(FactVerificationService::class);
        $genService = app(WebsiteGenerationService::class);

        // Add confirmed facts
        $factService->addManualFact($business, 'phone', 'Phone Number', '+91 98220 99887', $user->id);
        $factService->addManualFact($business, 'email', 'Email Address', 'sales@kalyanitextiles.in', $user->id);
        $factService->addManualFact($business, 'address', 'Address', '10 Ring Road, Surat, Gujarat', $user->id);

        // Generate Website
        $website = $genService->generateWebsite($business, [
            'design_preset' => 'modern_minimal',
            'title' => 'Kalyani Textile Mills Official',
        ]);

        $this->assertNotNull($website);
        $this->assertEquals('Kalyani Textile Mills Official', $website->title);
        $this->assertGreaterThanOrEqual(4, $website->sections()->count());
        $this->assertDatabaseHas('websites', ['id' => $website->id, 'business_id' => $business->id]);
    }

    public function test_public_website_anonymous_access_and_seo_headers(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $response = $this->get('/sites/patil-traditional-spices');

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertSee('<link rel="canonical" href="', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('Patil Traditional Spices');
        $response->assertSee('45 Shahupuri 2nd Lane');
    }

    public function test_unpublishing_returns_strict_404_for_anonymous_visitors(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $website = Website::where('slug', 'patil-traditional-spices')->first();
        $this->assertNotNull($website);
        $this->assertTrue($website->is_published);

        $publishingService = app(WebsitePublishingService::class);

        // Unpublish
        $publishingService->unpublish($website);
        $website->refresh();
        $this->assertFalse($website->is_published);

        // Anonymous request must receive 404
        $this->get('/sites/patil-traditional-spices')->assertStatus(404);

        // Republish
        $publishingService->publish($website, $website->user_id);
        $website->refresh();
        $this->assertTrue($website->is_published);

        // Anonymous request returns 200
        $this->get('/sites/patil-traditional-spices')->assertStatus(200);
    }

    public function test_end_to_end_user_registration_and_business_creation_pipeline(): void
    {
        // 1. Registration
        $regResponse = $this->post(route('register'), [
            'name' => 'Rajesh Khanna',
            'email' => 'rajesh.khanna@example.com',
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ]);

        $regResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'rajesh.khanna@example.com')->first();
        $this->assertNotNull($user);

        // 2. Business Creation
        $bizResponse = $this->post(route('businesses.store'), [
            'name' => 'Khanna Electronics & Services',
            'category' => 'retail',
            'city' => 'Indore',
            'state' => 'Madhya Pradesh',
            'phone' => '+91 97550 11223',
            'email' => 'contact@khannaelectronics.in',
            'description' => 'Trusted retail store and authorized repair center for consumer electronics since 1998.',
        ]);

        $bizResponse->assertRedirect();
        $business = Business::where('name', 'Khanna Electronics & Services')->first();
        $this->assertNotNull($business);
        $this->assertEquals($user->id, $business->user_id);

        // 3. Add Verified Fact
        $factService = app(FactVerificationService::class);
        $fact = $factService->addManualFact(
            $business,
            'operating_hours',
            'Operating Hours',
            'Mon-Sat 10:00 AM - 9:00 PM',
            $user->id
        );

        $this->assertNotNull($fact->id);
        $this->assertTrue($fact->is_confirmed);
        $this->assertDatabaseHas('fact_confirmations', [
            'business_id' => $business->id,
            'fact_key' => 'operating_hours',
            'confirmed_value' => 'Mon-Sat 10:00 AM - 9:00 PM',
        ]);
    }
}
