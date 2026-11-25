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
use Database\Seeders\DemoBusinessesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseEighteenFinalSubmissionVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_submission_markdown_documents_exist_and_are_valid(): void
    {
        $requiredDocs = [
            'README.md',
            'DATABASE.md',
            'API.md',
            'DEPLOYMENT.md',
            'DECISIONS.md',
            'DEMO_SCRIPT.md',
            'SUBMISSION_EMAIL.md',
        ];

        foreach ($requiredDocs as $doc) {
            $path = base_path($doc);
            $this->assertFileExists($path, "Required submission document {$doc} is missing.");
            $this->assertGreaterThan(500, filesize($path), "Document {$doc} appears truncated or empty.");
        }
    }

    public function test_all_backup_and_deployment_scripts_exist(): void
    {
        $requiredScripts = [
            'scripts/backup-db.sh',
            'scripts/restore-db.sh',
            'scripts/deploy.sh',
        ];

        foreach ($requiredScripts as $script) {
            $path = base_path($script);
            $this->assertFileExists($path, "Required operations script {$script} is missing.");
            $this->assertGreaterThan(100, filesize($path), "Script {$script} appears truncated.");
        }
    }

    public function test_demo_businesses_and_live_sites_are_available(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        $this->get('/sites/patil-traditional-spices')->assertStatus(200);
        $this->get('/sites/dr-kulkarni-ayurveda')->assertStatus(200);
    }

    public function test_all_domain_models_and_migrations_are_in_sync(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);

        $source = BusinessSource::create([
            'business_id' => $business->id,
            'source_type' => 'website',
            'url' => 'https://example.com/biz',
            'title' => 'Example Biz',
            'status' => 'crawled',
        ]);

        $file = UploadedFile::create([
            'business_id' => $business->id,
            'original_name' => 'menu.txt',
            'file_path' => 'demo/menu.txt',
            'file_type' => 'txt',
            'file_size' => 100,
            'mime_type' => 'text/plain',
            'processing_status' => 'completed',
        ]);

        $fact = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'fact_key' => 'phone',
            'fact_name' => 'Phone',
            'fact_value' => '1234567890',
            'confidence_score' => 0.9,
            'is_confirmed' => true,
        ]);

        $confirmation = FactConfirmation::create([
            'business_id' => $business->id,
            'fact_key' => 'phone',
            'confirmed_value' => '1234567890',
            'resolution_type' => 'accepted',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
        ]);

        $website = Website::factory()->create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'is_published' => true,
        ]);

        $section = WebsiteSection::create([
            'website_id' => $website->id,
            'section_type' => 'hero',
            'order' => 0,
            'is_visible' => true,
            'content' => ['heading' => 'Welcome'],
        ]);

        $version = WebsiteVersion::create([
            'website_id' => $website->id,
            'version_number' => 1,
            'created_by' => $user->id,
            'snapshot' => ['title' => $website->title],
        ]);

        $publication = Publication::create([
            'website_id' => $website->id,
            'published_url' => url('/sites/' . $website->slug),
            'published_by' => $user->id,
            'published_at' => now(),
            'is_active' => true,
        ]);

        $this->assertNotNull($business->id);
        $this->assertNotNull($source->id);
        $this->assertNotNull($file->id);
        $this->assertNotNull($fact->id);
        $this->assertNotNull($confirmation->id);
        $this->assertNotNull($website->id);
        $this->assertNotNull($section->id);
        $this->assertNotNull($version->id);
        $this->assertNotNull($publication->id);
    }

    public function test_demo_account_authenticates_and_sees_full_portfolio(): void
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
        $dashResponse->assertSee('Patil Traditional Spices');
        $dashResponse->assertSee('Dr. Kulkarni Ayurvedic Wellness');
    }
}
