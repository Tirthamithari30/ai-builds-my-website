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
use App\Models\WebsiteVersion;
use App\Services\FactVerificationService;
use App\Services\WebsiteGenerationService;
use App\Services\WebsitePublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseNineSystemTestingAndBugFixingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Business $business;
    protected Website $website;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::factory()->create([
            'name' => 'Meera Deshmukh',
            'email' => 'meera@deshmukhagro.in',
        ]);

        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Deshmukh Organic Agro & Farm Products',
            'category' => 'retail',
            'city' => 'Satara',
            'state' => 'Maharashtra',
            'country' => 'India',
            'address' => 'Plot 88, MIDC Additional Area, Satara, Maharashtra 415004',
            'phone' => '+91 98221 44556',
            'email' => 'sales@deshmukhagro.in',
            'description' => 'Certified organic jaggery, indigenous cold-pressed oils, and farm-fresh pulses from Maharashtra.',
            'operating_hours' => ['raw' => 'Mon - Sat: 9:00 AM - 7:00 PM'],
            'products' => ['Organic Block Jaggery', 'Cold Pressed Groundnut Oil', 'Organic Tur Dal'],
            'services' => ['Bulk Farm Supplies', 'Direct Home Delivery'],
        ]);

        $service = app(WebsiteGenerationService::class);
        $this->website = $service->generateWebsite($this->business, [
            'title' => 'Deshmukh Organic Agro Official',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#15803d',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'products', 'services', 'contact', 'footer'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Authentication & Session Security Tests
    |--------------------------------------------------------------------------
    */

    public function test_registration_validates_required_fields_and_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'secret',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_authenticates_valid_credentials_and_rejects_invalid_ones(): void
    {
        // Valid login
        $loginResponse = $this->post(route('login'), [
            'email' => 'meera@deshmukhagro.in',
            'password' => 'password', // default factory password
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->user);

        // Logout
        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();

        // Invalid login
        $badResponse = $this->post(route('login'), [
            'email' => 'meera@deshmukhagro.in',
            'password' => 'wrong-password',
        ]);
        $badResponse->assertSessionHasErrors();
        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Business Information Submission & Special Character Escaping
    |--------------------------------------------------------------------------
    */

    public function test_business_creation_with_special_characters_persists_safely(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('businesses.store'), [
            'name' => "Rao's Organic & Sons <script>alert('xss')</script>",
            'category' => 'retail',
            'city' => 'Pune & Satara',
            'phone' => '+91 98765 43210',
            'email' => 'rao@organics.in',
            'description' => 'A & B "Special" Farm Produce — 100% pure.',
        ]);

        $response->assertRedirect();

        $created = Business::where('email', 'rao@organics.in')->first();
        $this->assertNotNull($created);
        $this->assertEquals("Rao's Organic & Sons <script>alert('xss')</script>", $created->name);

        // Verify view escapes raw tags safely
        $viewResponse = $this->get(route('businesses.show', $created));
        $viewResponse->assertStatus(200);
        $viewResponse->assertDontSee("<script>alert('xss')</script>", false);
        $viewResponse->assertSee("&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;", false);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. File Uploads: Special Characters, Size Ceiling & MIME Restrictions
    |--------------------------------------------------------------------------
    */

    public function test_file_upload_with_complex_filename_and_spaces_stores_safely(): void
    {
        $this->actingAs($this->user);

        $filename = "Deshmukh's Organic & Fresh [Rate-Card] (2026).txt";
        $content = "Product: Organic Jaggery\nPrice: ₹60/kg\nContact: +91 98221 44556";

        $file = HttpUploadedFile::fake()->createWithContent($filename, $content);

        $response = $this->post(route('businesses.files.store', $this->business), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $uploaded = UploadedFile::where('business_id', $this->business->id)->first();

        $this->assertNotNull($uploaded);
        $this->assertEquals($filename, $uploaded->original_name);
        $this->assertEquals('completed', $uploaded->processing_status);
        $this->assertTrue(Storage::disk('public')->exists($uploaded->file_path));
    }

    public function test_file_upload_rejects_unsupported_extensions(): void
    {
        $this->actingAs($this->user);

        $badFile = HttpUploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $response = $this->post(route('businesses.files.store', $this->business), [
            'file' => $badFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Website Generation with Full Section Suite
    |--------------------------------------------------------------------------
    */

    public function test_website_generation_supports_all_eleven_section_types(): void
    {
        $this->actingAs($this->user);

        $allSections = array_keys(WebsiteGenerationService::ALLOWED_SECTIONS);

        $service = app(WebsiteGenerationService::class);
        $website = $service->generateWebsite($this->business, [
            'title' => 'Deshmukh Complete Mega Site',
            'design_preset' => 'premium_elegant',
            'primary_color' => '#15803d',
            'secondary_color' => '#0f172a',
            'sections' => $allSections,
        ]);

        $this->assertCount(count($allSections), $website->sections);

        // Preview frame renders all 11 sections without error
        $previewFrameResponse = $this->get(route('websites.preview.frame', $website));
        $previewFrameResponse->assertStatus(200);

        foreach ($allSections as $secKey) {
            $previewFrameResponse->assertSee("id=\"{$secKey}\"", false);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Visual Editor: Order Re-indexing & Delete Minimum Guard
    |--------------------------------------------------------------------------
    */

    public function test_cannot_delete_last_remaining_section_in_editor(): void
    {
        $this->actingAs($this->user);

        // Leave only 1 section
        $sections = $this->website->sections()->get();
        foreach ($sections->skip(1) as $s) {
            $s->delete();
        }

        $this->assertEquals(1, $this->website->sections()->count());
        $lastSection = $this->website->sections()->first();

        // Attempt to delete the final section
        $response = $this->delete(route('sections.delete', $lastSection));
        $response->assertSessionHas('error');

        $this->assertEquals(1, $this->website->sections()->count());
    }

    public function test_deleting_section_reindexes_remaining_section_orders(): void
    {
        $this->actingAs($this->user);

        $sections = $this->website->sections()->orderBy('order', 'asc')->get();
        $secondSection = $sections[1];

        // Delete section at index 1
        $this->delete(route('sections.delete', $secondSection));

        $remaining = $this->website->sections()->orderBy('order', 'asc')->get();
        foreach ($remaining as $idx => $s) {
            $this->assertEquals($idx, $s->order);
        }
    }

    public function test_reordering_sections_up_and_down_exchanges_order_values(): void
    {
        $this->actingAs($this->user);

        $sections = $this->website->sections()->orderBy('order', 'asc')->get();
        $first = $sections[0];
        $second = $sections[1];

        $firstOriginalOrder = $first->order;
        $secondOriginalOrder = $second->order;

        // Move second section UP
        $response = $this->post(route('websites.sections.reorder', $this->website), [
            'section_id' => $second->id,
            'direction' => 'up',
        ]);
        $response->assertRedirect();

        $first->refresh();
        $second->refresh();

        $this->assertEquals($secondOriginalOrder, $first->order);
        $this->assertEquals($firstOriginalOrder, $second->order);
    }

    /*
    |--------------------------------------------------------------------------
    | 6. SEO Metadata Updates & Clearing Behavior
    |--------------------------------------------------------------------------
    */

    public function test_updating_seo_metadata_and_clearing_values(): void
    {
        $this->actingAs($this->user);

        // 1. Set custom SEO Title and OG image
        $this->put(route('websites.seo.update', $this->website), [
            'seo_title' => 'Custom Organic Store Satara',
            'meta_description' => 'Direct farm-to-table organic produce from Satara.',
            'og_image' => 'https://example.com/banner.jpg',
        ]);

        $this->website->refresh();
        $this->assertEquals('Custom Organic Store Satara', $this->website->settings['seo_title']);
        $this->assertEquals('https://example.com/banner.jpg', $this->website->settings['og_image']);

        // 2. Clear SEO Title and OG image back to empty
        $this->put(route('websites.seo.update', $this->website), [
            'seo_title' => '',
            'meta_description' => 'Updated meta description without custom OG image.',
            'og_image' => '',
        ]);

        $this->website->refresh();
        $this->assertArrayNotHasKey('seo_title', $this->website->settings);
        $this->assertArrayNotHasKey('og_image', $this->website->settings);
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Version History Snapshot and Rollback
    |--------------------------------------------------------------------------
    */

    public function test_version_rollback_restores_complete_website_snapshot(): void
    {
        $this->actingAs($this->user);

        // Capture original title
        $originalTitle = $this->website->title;
        $v1 = $this->website->versions()->first();

        // Modify website title and add a section
        $this->website->update(['title' => 'Changed Title']);
        app(WebsitePublishingService::class)->createSnapshot($this->website, 'Title changed', $this->user->id);

        $this->assertEquals('Changed Title', $this->website->fresh()->title);

        // Restore Version 1
        $restoreResponse = $this->post(route('websites.versions.restore', [$this->website, $v1]));
        $restoreResponse->assertRedirect();

        $this->website->refresh();
        $this->assertEquals($originalTitle, $this->website->title);
    }

    /*
    |--------------------------------------------------------------------------
    | 8. Publishing, Vanity Slugs & Draft Privacy Security
    |--------------------------------------------------------------------------
    */

    public function test_draft_website_strictly_returns_404_to_anonymous_visitors(): void
    {
        $this->assertFalse($this->website->is_published);

        $response = $this->get('/sites/' . $this->website->slug);
        $response->assertStatus(404);
    }

    public function test_published_website_accessible_to_anonymous_visitors_with_seo_and_canonical(): void
    {
        $this->actingAs($this->user);

        $pubService = app(WebsitePublishingService::class);
        $pubService->publish($this->website, $this->user->id);

        $this->website->refresh();
        $this->assertTrue($this->website->is_published);

        // Incognito public visit
        $this->app['auth']->logout();
        $publicUrl = '/sites/' . $this->website->slug;
        $response = $this->get($publicUrl);

        $response->assertStatus(200);
        $response->assertSee('Deshmukh Organic Agro');
        $response->assertSee('+91 98221 44556');
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertSee('<link rel="canonical" href="' . url($publicUrl) . '">', false);

        // Unpublish takes it offline immediately
        $this->actingAs($this->user);
        $this->post(route('websites.unpublish', $this->website));

        $this->app['auth']->logout();
        $this->get($publicUrl)->assertStatus(404);
    }
}
