<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Publication;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\DemoBusinessesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSixteenMultiBusinessDemonstrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_businesses_seeder_populates_both_businesses(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        // Verify Demo User
        $demoUser = User::where('email', 'demo@websites.co.in')->first();
        $this->assertNotNull($demoUser);

        // Verify Business 1: Patil Spices
        $patil = Business::where('name', 'Patil Traditional Spices & Foods')->first();
        $this->assertNotNull($patil);
        $this->assertEquals('retail', $patil->category);
        $this->assertEquals('Kolhapur', $patil->city);
        $this->assertCount(1, $patil->files);
        $this->assertCount(1, $patil->sources);
        $this->assertGreaterThanOrEqual(5, $patil->extractedFacts()->count());
        $this->assertGreaterThanOrEqual(5, $patil->factConfirmations()->count());

        // Verify Business 2: Dr. Kulkarni Ayurveda
        $kulkarni = Business::where('name', 'Dr. Kulkarni Ayurvedic Wellness Clinic')->first();
        $this->assertNotNull($kulkarni);
        $this->assertEquals('professional_services', $kulkarni->category);
        $this->assertEquals('Pune', $kulkarni->city);
        $this->assertCount(1, $kulkarni->files);
        $this->assertCount(1, $kulkarni->sources);
        $this->assertGreaterThanOrEqual(5, $kulkarni->extractedFacts()->count());
        $this->assertGreaterThanOrEqual(5, $kulkarni->factConfirmations()->count());
    }

    public function test_patil_traditional_spices_public_site_renders_grounded_content(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $response = $this->get('/sites/patil-traditional-spices');

        $response->assertStatus(200);
        $response->assertSee('Patil Traditional Spices');
        $response->assertSee('Kolhapur');
        $response->assertSee('45 Shahupuri 2nd Lane');
        $response->assertSee('+91 94220 54321');
        $response->assertSee('orders@patilspices.com');
        $response->assertSee('Kolhapuri Kanda Lasun Masala');
        $response->assertSee('Special Mutton &amp; Chicken Masala', false);
        $response->assertSee('Traditional Goda Masala');
        $response->assertSee('FSSAI');
        $response->assertSee('Established 1985');
    }

    public function test_dr_kulkarni_ayurvedic_clinic_public_site_renders_clinical_content(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $response = $this->get('/sites/dr-kulkarni-ayurveda');

        $response->assertStatus(200);
        $response->assertSee('Dr. Kulkarni Ayurvedic Wellness');
        $response->assertSee('Classical Ayurveda');
        $response->assertSee('Nadi Pariksha');
        $response->assertSee('Panchakarma');
        $response->assertSee('Plot 18, Mayur Colony, Kothrud');
        $response->assertSee('Pune');
        $response->assertSee('+91 98230 12345');
        $response->assertSee('contact@ayurvedapune.in');
        $response->assertSee('Dr. Ananya Kulkarni');
        $response->assertSee('BAMS, MD');
    }

    public function test_demo_populate_artisan_command_executes_cleanly(): void
    {
        $this->artisan('demo:populate')
            ->expectsOutputToContain('Populating authentic multi-business demonstration data')
            ->expectsOutputToContain('/sites/patil-traditional-spices')
            ->expectsOutputToContain('/sites/dr-kulkarni-ayurveda')
            ->assertExitCode(0);

        $this->assertDatabaseHas('websites', ['slug' => 'patil-traditional-spices', 'is_published' => true]);
        $this->assertDatabaseHas('websites', ['slug' => 'dr-kulkarni-ayurveda', 'is_published' => true]);
    }

    public function test_demo_user_can_authenticate_and_access_dashboard(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $loginResponse = $this->post(route('login'), [
            'email' => 'demo@websites.co.in',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Patil Traditional Spices &amp; Foods', false);
        $dashResponse->assertSee('Dr. Kulkarni Ayurvedic Wellness Clinic');
        $dashResponse->assertSee('Published');
    }

    public function test_demo_websites_have_active_publication_records(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $patilWebsite = Website::where('slug', 'patil-traditional-spices')->first();
        $this->assertNotNull($patilWebsite);
        $this->assertTrue($patilWebsite->is_published);

        $pub1 = Publication::where('website_id', $patilWebsite->id)->where('is_active', true)->first();
        $this->assertNotNull($pub1);
        $this->assertStringContainsString('/sites/patil-traditional-spices', $pub1->published_url);

        $kulkarniWebsite = Website::where('slug', 'dr-kulkarni-ayurveda')->first();
        $this->assertNotNull($kulkarniWebsite);
        $this->assertTrue($kulkarniWebsite->is_published);

        $pub2 = Publication::where('website_id', $kulkarniWebsite->id)->where('is_active', true)->first();
        $this->assertNotNull($pub2);
        $this->assertStringContainsString('/sites/dr-kulkarni-ayurveda', $pub2->published_url);
    }
}
