<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\ExtractedFact;
use App\Models\Publication;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Services\FactVerificationService;
use App\Services\SourceExtractionService;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseEightEndToEndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Complete End-to-End Workflow for Business 1:
     * Patil Traditional Spices & Foods (Kolhapur, Retail / Food Producer)
     */
    public function test_complete_e2e_workflow_business_one_spices_retail(): void
    {
        // 1. User Registration & Authentication
        $user = User::factory()->create([
            'name' => 'Vikram Patil',
            'email' => 'vikram@patilspices.com',
        ]);
        $this->actingAs($user);

        // 2. View Dashboard (Empty state initially)
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertStatus(200);

        // 3. Create Business Profile with authentic information
        $businessData = [
            'name' => 'Patil Traditional Spices & Foods',
            'category' => 'retail',
            'city' => 'Kolhapur',
            'state' => 'Maharashtra',
            'country' => 'India',
            'address' => '45 Shahupuri 2nd Lane, Kolhapur, Maharashtra 416001',
            'phone' => '+91 94220 54321',
            'email' => 'orders@patilspices.com',
            'description' => 'Authentic Kolhapuri spice blends, kanda lasun masala, and traditional Maharashtrian culinary staples crafted with handpicked ingredients since 1985.',
            'operating_hours' => 'Monday - Saturday: 9:30 AM - 8:30 PM',
            'products' => "Kolhapuri Kanda Lasun Masala\nSpecial Mutton & Chicken Masala\nTraditional Goda Masala\nHandmade Jowar Bhakri Flour",
            'services' => "Custom Bulk Spice Grinding\nPan-India Courier Delivery",
        ];

        $createResponse = $this->post(route('businesses.store'), $businessData);
        $createResponse->assertRedirect();

        $business = Business::where('name', 'Patil Traditional Spices & Foods')->first();
        $this->assertNotNull($business);
        $this->assertEquals($user->id, $business->user_id);

        // 4. Document Upload: Price List & Brochure (TXT format)
        $documentText = <<<TXT
Patil Traditional Spices & Foods
Official Product Catalog & Price List
Location: 45 Shahupuri 2nd Lane, Kolhapur
Direct Phone: +91 94220 54321
Support Email: orders@patilspices.com

Available Products:
- Kolhapuri Kanda Lasun Masala (500g): ₹180
- Special Mutton Masala (250g): ₹140
- Traditional Goda Masala (200g): ₹110
- Premium Jowar Bhakri Flour (1kg): ₹75

Store Timings: Monday to Saturday 9:30 AM to 8:30 PM. Closed on Sundays.
Custom bulk orders available for weddings, hotels, and retail distributors.
TXT;

        $fakeFile = HttpUploadedFile::fake()->createWithContent('Patil_Spices_Catalog.txt', $documentText);
        $uploadResponse = $this->post(route('businesses.files.store', $business), [
            'file' => $fakeFile,
        ]);
        $uploadResponse->assertRedirect();

        $uploadedDoc = UploadedFile::where('business_id', $business->id)->first();
        $this->assertNotNull($uploadedDoc);
        $this->assertEquals('completed', $uploadedDoc->processing_status);
        $this->assertStringContainsString('Kolhapuri Kanda Lasun Masala', $uploadedDoc->extracted_text);

        // 5. Verify Extracted Facts from Uploaded Document
        // File processing automatically extracts facts into database
        $this->assertDatabaseHas('extracted_facts', [
            'business_id' => $business->id,
            'source_type' => 'document',
        ]);

        // 6. Fact Verification & Review
        $factService = app(FactVerificationService::class);
        $analysis = $factService->analyzeFacts($business);

        $this->assertFalse($analysis['has_blocking_conflicts']);
        $this->assertArrayHasKey('phone', $analysis['unverified']);

        // Owner confirms unverified fact
        $phoneFact = ExtractedFact::where('business_id', $business->id)->where('fact_key', 'phone')->first();
        if ($phoneFact) {
            $confirmResponse = $this->post(route('facts.confirm', $phoneFact));
            $confirmResponse->assertRedirect();
            $phoneFact->refresh();
            $this->assertEquals('user-confirmed', $phoneFact->verification_status);

            // Verified dictionary now contains confirmed phone
            $freshAnalysis = $factService->analyzeFacts($business);
            $this->assertArrayHasKey('phone', $freshAnalysis['confirmed']);
        }

        // 7. Website Generation Engine
        $genService = app(WebsiteGenerationService::class);
        $website = $genService->generateWebsite($business, [
            'title' => 'Patil Traditional Spices Official',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#b91c1c', // Deep rich spice red
            'secondary_color' => '#1e293b',
            'sections' => ['hero', 'about', 'products', 'services', 'contact', 'footer'],
        ]);

        $this->assertNotNull($website);
        $this->assertFalse($website->is_published);
        $this->assertEquals(6, $website->sections()->count());

        // 8. Visual Website Editor: Edit Hero Section Content
        $heroSec = $website->sections()->where('section_type', 'hero')->first();
        $this->assertNotNull($heroSec);

        $updatedHeroContent = $heroSec->content;
        $updatedHeroContent['heading'] = 'Handcrafted Kolhapuri Masalas & Spices';
        $updatedHeroContent['subheading'] = 'Directly from the spice capital of Maharashtra since 1985';

        $editResponse = $this->put(route('sections.update', $heroSec), [
            'content' => $updatedHeroContent,
        ]);
        $editResponse->assertRedirect();

        $heroSec->refresh();
        $this->assertEquals('Handcrafted Kolhapuri Masalas & Spices', $heroSec->content['heading']);

        // 9. Visual Website Editor: Section Regeneration Endpoint
        $aboutSec = $website->sections()->where('section_type', 'about')->first();
        $regenPreviewResponse = $this->postJson(route('sections.regenerate-preview', $aboutSec), [
            'user_prompt' => 'Emphasize the 40-year heritage of roasting spices in Kolhapur',
        ]);
        $regenPreviewResponse->assertStatus(200);
        $regenData = $regenPreviewResponse->json();
        $this->assertTrue($regenData['success']);
        $this->assertNotNull($regenData['proposed_content']);

        // Apply owner-approved regeneration
        $applyResponse = $this->postJson(route('sections.apply-regeneration', $aboutSec), [
            'content' => $regenData['proposed_content'],
        ]);
        $applyResponse->assertStatus(200);
        $this->assertTrue($applyResponse->json('success'));

        // 10. Responsive Preview Check
        $previewResponse = $this->get(route('websites.preview', $website));
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('Patil Traditional Spices Official');
        $previewResponse->assertSee('Handcrafted Kolhapuri Masalas &amp; Spices', false);

        // 11. Public Publishing Workflow
        $publishResponse = $this->post(route('websites.publish', $website));
        $publishResponse->assertRedirect();
        $publishResponse->assertSessionHas('success');

        $website->refresh();
        $this->assertTrue($website->is_published);
        $this->assertNotNull($website->published_at);

        // Verify active publication
        $activePub = Publication::where('website_id', $website->id)->where('is_active', true)->first();
        $this->assertNotNull($activePub);
        $this->assertEquals(url('/sites/' . $website->slug), $activePub->published_url);

        // 12. Anonymous Incognito Public Access
        $this->app['auth']->logout(); // Ensure visitor is unauthenticated

        $publicUrl = '/sites/' . $website->slug;
        $publicResponse = $this->get($publicUrl);
        $publicResponse->assertStatus(200);

        // Verify factual assertions on public website
        $publicResponse->assertSee('Patil Traditional Spices');
        $publicResponse->assertSee('Kolhapur');
        $publicResponse->assertSee('+91 94220 54321');
        $publicResponse->assertSee('Kolhapuri Kanda Lasun Masala');

        // Verify SEO & Robots Tags
        $publicResponse->assertSee('<meta name="robots" content="index, follow">', false);
        $publicResponse->assertSee('<link rel="canonical" href="' . url($publicUrl) . '">', false);
        $publicResponse->assertSee('<meta property="og:type" content="website">', false);

        // 13. Unpublish & Verify Privacy
        $this->actingAs($user);
        $unpublishResponse = $this->post(route('websites.unpublish', $website));
        $unpublishResponse->assertRedirect();

        $this->app['auth']->logout();
        $this->get($publicUrl)->assertStatus(404);
    }

    /**
     * Complete End-to-End Workflow for Business 2:
     * Dr. Kulkarni Ayurvedic Wellness & Panchakarma Clinic (Pune, Professional Healthcare)
     */
    public function test_complete_e2e_workflow_business_two_ayurvedic_healthcare(): void
    {
        // 1. User Registration & Authentication
        $user = User::factory()->create([
            'name' => 'Dr. Ananya Kulkarni',
            'email' => 'dr.kulkarni@ayurvedapune.in',
        ]);
        $this->actingAs($user);

        // 2. Create Business Profile with authentic clinical information
        $businessData = [
            'name' => 'Dr. Kulkarni Ayurvedic Wellness & Panchakarma Clinic',
            'category' => 'professional_services',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'country' => 'India',
            'address' => '12 Bhandarkar Road, Deccan Gymkhana, Pune, Maharashtra 411004',
            'phone' => '+91 98220 12345',
            'email' => 'appointments@kulkarniayurveda.in',
            'description' => 'Classical Ayurvedic healthcare clinic specializing in customized Panchakarma detox, chronic lifestyle disorder management, and authentic herbal rejuvenation therapies.',
            'operating_hours' => 'Monday - Saturday: 8:00 AM - 1:00 PM, 5:00 PM - 8:30 PM',
            'services' => "Nadi Pariksha (Pulse Diagnosis)\nAbhyanga Full-Body Herbal Massage\nShirodhara Stress Management\nPanchakarma Detoxification Therapy\nCustomized Herbal Dietary Consultations",
            'additional_info' => 'Consultations by prior appointment only. Certified BAMS and MD Ayurveda physicians.',
        ];

        $createResponse = $this->post(route('businesses.store'), $businessData);
        $createResponse->assertRedirect();

        $business = Business::where('name', 'Dr. Kulkarni Ayurvedic Wellness & Panchakarma Clinic')->first();
        $this->assertNotNull($business);

        // 3. Add Web Source (Simulated safe crawl with public clinical details)
        Http::fake([
            'https://kulkarniayurveda.in/services' => Http::response(<<<HTML
<!DOCTYPE html>
<html>
<head><title>Dr. Kulkarni Ayurveda — Panchakarma Treatments</title></head>
<body>
    <h1>Ayurvedic Panchakarma Clinic Pune</h1>
    <p>Established on Bhandarkar Road, Deccan Gymkhana, Pune.</p>
    <p>Doctor: Dr. Ananya Kulkarni (BAMS, MD Ayurveda)</p>
    <p>Direct Clinic Reception: +91 98220 12345</p>
    <p>Email: appointments@kulkarniayurveda.in</p>
    <div>
        <h3>Key Ayurvedic Treatments</h3>
        <ul>
            <li>Shirodhara for insomnia and stress relief</li>
            <li>Abhyanga therapeutic herbal oil massage</li>
            <li>Kati Basti for spine and lower back pain</li>
            <li>Panchakarma 7-day residential detox protocol</li>
        </ul>
    </div>
</body>
</html>
HTML, 200),
        ]);

        $sourceResponse = $this->post(route('businesses.sources.store', $business), [
            'source_type' => 'url',
            'url' => 'https://kulkarniayurveda.in/services',
        ]);
        $sourceResponse->assertRedirect();

        $source = BusinessSource::where('business_id', $business->id)->first();
        $this->assertNotNull($source);
        $this->assertEquals('crawled', $source->status);
        $this->assertStringContainsString('Shirodhara', $source->raw_content);

        // 4. Verify Structured Facts extracted from Web Source
        $this->assertDatabaseHas('extracted_facts', [
            'business_id' => $business->id,
            'source_type' => 'webpage',
        ]);

        // 5. Fact Verification & Review
        $factService = app(FactVerificationService::class);
        $analysis = $factService->analyzeFacts($business);
        $this->assertFalse($analysis['has_blocking_conflicts']);
        $this->assertArrayHasKey('services', $analysis['confirmed']);

        // 6. Generate Website using Premium Elegant Preset
        $genService = app(WebsiteGenerationService::class);
        $website = $genService->generateWebsite($business, [
            'title' => 'Dr. Kulkarni Ayurveda Pune',
            'design_preset' => 'premium_elegant',
            'primary_color' => '#065f46', // Emerald herbal green
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'services', 'features', 'contact', 'footer'],
        ]);

        $this->assertNotNull($website);
        $this->assertEquals('premium_elegant', $website->design_preset);
        $this->assertEquals('#065f46', $website->primary_color);

        // 7. Update Vanity Slug and SEO Metadata
        $slugResponse = $this->put(route('websites.slug.update', $website), [
            'slug' => 'kulkarni-ayurveda-pune',
        ]);
        $slugResponse->assertRedirect();
        $website->refresh();
        $this->assertEquals('kulkarni-ayurveda-pune', $website->slug);

        $seoResponse = $this->put(route('websites.seo.update', $website), [
            'seo_title' => 'Dr. Kulkarni Ayurveda & Panchakarma Clinic | Pune',
            'meta_description' => 'Authentic Ayurvedic healthcare, Panchakarma detoxification, and Nadi Pariksha pulse diagnosis in Deccan Gymkhana, Pune.',
            'og_image' => 'https://example.com/clinic-deccan.jpg',
        ]);
        $seoResponse->assertRedirect();
        $website->refresh();
        $this->assertEquals('Dr. Kulkarni Ayurveda & Panchakarma Clinic | Pune', $website->settings['seo_title']);

        // 8. Publish Website
        $pubResponse = $this->post(route('websites.publish', $website));
        $pubResponse->assertRedirect();

        $website->refresh();
        $this->assertTrue($website->is_published);

        // 9. Anonymous Public Verification
        $this->app['auth']->logout();
        $publicUrl = '/sites/kulkarni-ayurveda-pune';
        $publicResponse = $this->get($publicUrl);

        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Dr. Kulkarni Ayurveda &amp; Panchakarma Clinic', false);
        $publicResponse->assertSee('Bhandarkar Road, Deccan Gymkhana, Pune');
        $publicResponse->assertSee('+91 98220 12345');
        $publicResponse->assertSee('Shirodhara');
        $publicResponse->assertSee('<title>Dr. Kulkarni Ayurveda &amp; Panchakarma Clinic | Pune</title>', false);
        $publicResponse->assertSee('<meta name="robots" content="index, follow">', false);
    }
}
