<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Services\AiService;
use App\Services\DocumentProcessingService;
use App\Services\FactVerificationService;
use App\Services\PdfProcessingService;
use App\Services\SourceExtractionService;
use App\Services\WebResearchService;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhaseFifteenArchitectureAndRefactoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_factory_creates_valid_business_instance(): void
    {
        $business = Business::factory()->create();

        $this->assertNotNull($business->id);
        $this->assertNotNull($business->user_id);
        $this->assertNotEmpty($business->name);
        $this->assertNotEmpty($business->category);
        $this->assertDatabaseHas('businesses', ['id' => $business->id]);
    }

    public function test_website_factory_creates_valid_website_instance(): void
    {
        $website = Website::factory()->create();

        $this->assertNotNull($website->id);
        $this->assertNotNull($website->business_id);
        $this->assertNotNull($website->user_id);
        $this->assertNotEmpty($website->title);
        $this->assertNotEmpty($website->slug);
        $this->assertDatabaseHas('websites', ['id' => $website->id]);
    }

    public function test_service_container_resolves_all_domain_services(): void
    {
        $services = [
            AiService::class,
            FactVerificationService::class,
            WebsiteGenerationService::class,
            WebsitePublishingService::class,
            PdfProcessingService::class,
            DocumentProcessingService::class,
            WebResearchService::class,
            SourceExtractionService::class,
        ];

        foreach ($services as $serviceClass) {
            $instance = app($serviceClass);
            $this->assertInstanceOf($serviceClass, $instance, "Service container failed to resolve {$serviceClass}");
        }
    }

    public function test_fact_verification_service_analyzes_facts_with_batched_queries(): void
    {
        $business = Business::factory()->create();

        // Create extracted facts for multiple keys
        foreach (['phone', 'email', 'address', 'description', 'services', 'operating_hours'] as $key) {
            ExtractedFact::create([
                'business_id' => $business->id,
                'source_type' => 'document',
                'fact_key' => $key,
                'fact_name' => ucwords($key),
                'fact_value' => "Sample value for {$key}",
                'verification_status' => 'user-confirmed',
                'confidence_score' => 0.95,
                'is_confirmed' => true,
            ]);

            FactConfirmation::create([
                'business_id' => $business->id,
                'fact_key' => $key,
                'confirmed_value' => "Confirmed value for {$key}",
                'resolution_type' => 'accepted',
                'confirmed_by' => $business->user_id,
                'confirmed_at' => now(),
            ]);
        }

        $service = app(FactVerificationService::class);

        // Count queries executed during analyzeFacts
        DB::enableQueryLog();
        $analysis = $service->analyzeFacts($business);
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertIsArray($analysis);
        $this->assertCount(6, $analysis['confirmed']);
        $this->assertFalse($analysis['has_blocking_conflicts']);
        // Query count should be exactly 2: 1 for ExtractedFact, 1 for FactConfirmation
        $this->assertLessThanOrEqual(3, $queryCount, "analyzeFacts exceeded expected batched query count (actual: {$queryCount})");
    }

    public function test_website_publishing_service_reuses_loaded_sections(): void
    {
        $website = Website::factory()->create();
        WebsiteSection::create([
            'website_id' => $website->id,
            'section_type' => 'hero',
            'order' => 1,
            'is_visible' => true,
            'content' => ['heading' => 'Hello World'],
        ]);

        $website->load('sections');

        $publishingService = app(WebsitePublishingService::class);

        DB::enableQueryLog();
        $validation = $publishingService->validateForPublishing($website);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertTrue($validation['is_valid']);
        // Assert no query was made to `website_sections` because sections was pre-loaded
        $sectionQueries = array_filter($queries, fn($q) => str_contains($q['query'], 'website_sections'));
        $this->assertCount(0, $sectionQueries, "Redundant queries to website_sections detected when relation was already loaded.");
    }

    public function test_operating_hours_clean_serialization(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('businesses.store'), [
            'name' => 'Clean Hours Cafe',
            'category' => 'cafe',
            'operating_hours' => 'Mon-Fri: 8am-4pm',
        ]);

        $response->assertStatus(302);

        $business = Business::where('name', 'Clean Hours Cafe')->first();
        $this->assertNotNull($business);

        // Assert single-level raw array: ['raw' => 'Mon-Fri: 8am-4pm']
        $this->assertIsArray($business->operating_hours);
        $this->assertArrayHasKey('raw', $business->operating_hours);
        $this->assertIsString($business->operating_hours['raw']);
        $this->assertEquals('Mon-Fri: 8am-4pm', $business->operating_hours['raw']);
    }

    public function test_dashboard_controller_efficiently_loads_relationships(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['user_id' => $user->id]);
        $website = Website::factory()->create(['business_id' => $business->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('businesses');
        $businesses = $response->viewData('businesses');
        $this->assertTrue($businesses->first()->relationLoaded('latestWebsite'));
        $this->assertTrue($businesses->first()->relationLoaded('sources'));
        $this->assertTrue($businesses->first()->relationLoaded('files'));
        $this->assertTrue($businesses->first()->relationLoaded('extractedFacts'));
    }
}
