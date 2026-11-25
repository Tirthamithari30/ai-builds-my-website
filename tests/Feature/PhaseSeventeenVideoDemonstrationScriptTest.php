<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\DemoBusinessesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSeventeenVideoDemonstrationScriptTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_script_markdown_file_exists_and_contains_complete_screenplay(): void
    {
        $scriptPath = base_path('DEMO_SCRIPT.md');
        $this->assertFileExists($scriptPath);

        $content = file_get_contents($scriptPath);
        $this->assertStringContainsString('Five-Minute Video Demonstration Script', $content);
        $this->assertStringContainsString('Patil Traditional Spices & Foods', $content);
        $this->assertStringContainsString('Dr. Kulkarni Ayurvedic Wellness Clinic', $content);
        $this->assertStringContainsString('/sites/patil-traditional-spices', $content);
        $this->assertStringContainsString('/sites/dr-kulkarni-ayurveda', $content);
        $this->assertStringContainsString('demo@websites.co.in', $content);
        $this->assertStringContainsString('159 automated tests', $content);
        $this->assertStringContainsString('Presenter Recording Checklist', $content);
    }

    public function test_all_walkthrough_routes_in_screenplay_are_accessible(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $demoUser = User::where('email', 'demo@websites.co.in')->first();
        $this->assertNotNull($demoUser);

        $patilBusiness = Business::where('name', 'Patil Traditional Spices & Foods')->first();
        $this->assertNotNull($patilBusiness);
        $patilWebsite = Website::where('business_id', $patilBusiness->id)->first();
        $this->assertNotNull($patilWebsite);

        // 1. Unauthenticated Landing & Auth routes
        $this->get('/')->assertStatus(200);
        $this->get(route('login'))->assertStatus(200);
        $this->get(route('register'))->assertStatus(200);

        // 2. Authenticated Dashboard & Project Routes
        $this->actingAs($demoUser);
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('businesses.create'))->assertStatus(200);
        $this->get(route('businesses.show', $patilBusiness))->assertStatus(200);
        $this->get(route('businesses.sources.index', $patilBusiness))->assertStatus(200);
        $this->get(route('businesses.facts.index', $patilBusiness))->assertStatus(200);
        $this->get(route('businesses.website.setup', $patilBusiness))->assertStatus(200);

        // 3. Visual Editor & Preview Routes
        $this->get(route('websites.edit', $patilWebsite))->assertStatus(200);
        $this->get(route('websites.preview', $patilWebsite))->assertStatus(200);

        // 4. Anonymous Live Public Sites
        $this->app['auth']->logout();
        $this->get('/sites/patil-traditional-spices')->assertStatus(200);
        $this->get('/sites/dr-kulkarni-ayurveda')->assertStatus(200);
    }

    public function test_screenplay_demo_credentials_authenticate_cleanly(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $response = $this->post(route('login'), [
            'email' => 'demo@websites.co.in',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }
}
