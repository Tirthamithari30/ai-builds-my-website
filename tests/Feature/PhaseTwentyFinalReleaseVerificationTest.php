<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Publication;
use App\Models\User;
use App\Models\Website;
use Database\Seeders\DemoBusinessesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PhaseTwentyFinalReleaseVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_release_documentation_files_exist_and_are_populated(): void
    {
        $requiredDocs = [
            'README.md',
            'PROJECT_DOCUMENTATION.md',
            'DEPLOYMENT.md',
            'DATABASE.md',
            'API.md',
            'docs/USER_GUIDE.md',
            'docs/INSTALLATION_GUIDE.md',
            'docs/DEMO_SCRIPT_7MIN.md',
            '.env.example',
            '.env.production.example',
        ];

        foreach ($requiredDocs as $doc) {
            $path = base_path($doc);
            $this->assertFileExists($path, "Required release document [{$doc}] must exist.");
            $this->assertGreaterThan(200, File::size($path), "Document [{$doc}] must not be empty.");
        }
    }

    public function test_security_cleanliness_and_no_exposed_secrets_in_config_templates(): void
    {
        $envExample = File::get(base_path('.env.example'));
        $envProdExample = File::get(base_path('.env.production.example'));

        // Real Google AI Studio keys begin with AIzaSy
        $this->assertStringNotContainsString('AIzaSy', $envExample);
        $this->assertStringNotContainsString('AIzaSy', $envProdExample);

        // Gitignore check
        $gitignore = File::get(base_path('.gitignore'));
        $this->assertStringContainsString('.env', $gitignore);
        $this->assertStringContainsString('/vendor', $gitignore);
        $this->assertStringContainsString('/node_modules', $gitignore);
    }

    public function test_database_schema_completeness_across_all_ten_tables(): void
    {
        $expectedTables = [
            'users',
            'businesses',
            'uploaded_files',
            'business_sources',
            'extracted_facts',
            'fact_confirmations',
            'websites',
            'website_sections',
            'website_versions',
            'publications',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Database table [{$table}] must exist.");
        }
    }

    public function test_seeded_demo_businesses_are_fully_operational_and_publicly_renderable(): void
    {
        $this->seed(DemoBusinessesSeeder::class);

        // Verify seeded users & businesses
        $demoUser = User::where('email', 'demo@websites.co.in')->first();
        $this->assertNotNull($demoUser);

        $patil = Business::where('name', 'Patil Traditional Spices & Foods')->first();
        $this->assertNotNull($patil);

        $kulkarni = Business::where('name', 'Dr. Kulkarni Ayurvedic Wellness Clinic')->first();
        $this->assertNotNull($kulkarni);

        // Verify public sites render HTTP 200 with verified content
        $patilResponse = $this->get('/sites/patil-traditional-spices');
        $patilResponse->assertStatus(200);
        $patilResponse->assertSee('Patil Traditional Spices');
        $patilResponse->assertSee('+91 94220 54321');

        $kulkarniResponse = $this->get('/sites/dr-kulkarni-ayurveda');
        $kulkarniResponse->assertStatus(200);
        $kulkarniResponse->assertSee('Dr. Kulkarni');
        $kulkarniResponse->assertSee('+91 98230 12345');
    }

    public function test_project_documentation_covers_all_twenty_mandatory_technical_sections(): void
    {
        $doc = File::get(base_path('PROJECT_DOCUMENTATION.md'));

        $requiredSections = [
            '1. Project Introduction',
            '2. Abstract',
            '3. Problem Statement',
            '4. Project Objectives',
            '5. Existing System',
            '6. Proposed System',
            '7. Technology Stack',
            '8. System Architecture',
            '9. Database Design',
            '10. Entity-Relationship (ER) Diagram',
            '11. Data Flow Diagram (DFD)',
            '12. Module Descriptions',
            '13. Gemini AI Integration',
            '14. Website Generation Workflow',
            '15. Security Implementation',
            '16. Testing Results',
            '17. Deployment Architecture',
            '18. Limitations',
            '19. Future Scope',
            '20. Conclusion',
        ];

        foreach ($requiredSections as $section) {
            $this->assertStringContainsString($section, $doc, "PROJECT_DOCUMENTATION.md must contain [{$section}].");
        }
    }
}
