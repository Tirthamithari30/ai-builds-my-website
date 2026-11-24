<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\ExtractedFact;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Services\WebResearchService;
use App\Services\WebsiteGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseTenSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $victim;
    protected User $attacker;
    protected Business $victimBusiness;
    protected Website $victimWebsite;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->victim = User::factory()->create([
            'name' => 'Victim User',
            'email' => 'victim@company.com',
        ]);

        $this->attacker = User::factory()->create([
            'name' => 'Attacker User',
            'email' => 'attacker@evil.com',
        ]);

        $this->victimBusiness = Business::create([
            'user_id' => $this->victim->id,
            'name' => 'Victim Secure Enterprise',
            'category' => 'consultant',
            'city' => 'Mumbai',
            'phone' => '+91 98200 11223',
            'email' => 'contact@victimsecure.com',
            'description' => 'Confidential enterprise business operations.',
        ]);

        $service = app(WebsiteGenerationService::class);
        $this->victimWebsite = $service->generateWebsite($this->victimBusiness, [
            'title' => 'Victim Enterprise Official',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'contact', 'footer'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Authentication Brute-Force Rate Limiting
    |--------------------------------------------------------------------------
    */

    public function test_login_throttles_after_five_consecutive_failed_attempts(): void
    {
        // 5 consecutive failed attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login'), [
                'email' => 'victim@company.com',
                'password' => 'wrong-pass-' . $i,
            ]);
            $response->assertSessionHasErrors('email');
            $this->assertStringContainsString('credentials do not match', session('errors')->first('email'));
        }

        // 6th attempt should be blocked by RateLimiter
        $blockedResponse = $this->post(route('login'), [
            'email' => 'victim@company.com',
            'password' => 'another-wrong-pass',
        ]);

        $blockedResponse->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
    }

    public function test_successful_login_clears_rate_limiter(): void
    {
        // 2 failed attempts
        $this->post(route('login'), ['email' => 'victim@company.com', 'password' => 'wrong']);
        $this->post(route('login'), ['email' => 'victim@company.com', 'password' => 'wrong']);

        // Successful login
        $successResponse = $this->post(route('login'), [
            'email' => 'victim@company.com',
            'password' => 'password',
        ]);
        $successResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->victim);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Stored XSS Prevention & CSS Context Breakout
    |--------------------------------------------------------------------------
    */

    public function test_font_family_rejects_script_and_css_breakout_payloads(): void
    {
        $this->actingAs($this->victim);

        $maliciousPayload = "';}</style><script>alert('xss')</script>";

        $response = $this->put(route('websites.design.update', $this->victimWebsite), [
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'font_family' => $maliciousPayload,
        ]);

        $response->assertSessionHasErrors('font_family');
    }

    public function test_template_strips_malicious_characters_from_font_family_even_if_bypassed(): void
    {
        // Forcibly set payload in database directly
        $this->victimWebsite->update([
            'font_family' => "';}</style><script>alert('pwned')</script>",
            'is_published' => true,
        ]);

        $response = $this->get('/sites/' . $this->victimWebsite->slug);
        $response->assertStatus(200);

        // Verify <script> tag does not execute and was sanitized
        $response->assertDontSee("<script>alert('pwned')</script>", false);
        $response->assertDontSee("</style><script>", false);
    }

    public function test_business_attributes_strictly_escape_html_tags_in_blade(): void
    {
        $this->actingAs($this->victim);

        $xssName = "Acme Corp <img src=x onerror=alert(1)>";
        $xssDesc = "Trusted partner <script>alert('attack')</script>";

        $this->victimBusiness->update([
            'name' => $xssName,
            'description' => $xssDesc,
        ]);

        $response = $this->get(route('businesses.show', $this->victimBusiness));
        $response->assertStatus(200);

        // Blade auto-escaping converts tags to entities
        $response->assertDontSee("<img src=x onerror=alert(1)>", false);
        $response->assertDontSee("<script>alert('attack')</script>", false);
        $response->assertSee("&lt;img src=x onerror=alert(1)&gt;", false);
        $response->assertSee("&lt;script&gt;alert(&#039;attack&#039;)&lt;/script&gt;", false);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. SSRF Protection: Loopback, Cloud Metadata & Malicious Redirects
    |--------------------------------------------------------------------------
    */

    public function test_ssrf_validator_blocks_internal_and_cloud_metadata_ips(): void
    {
        $webService = app(WebResearchService::class);

        $badUrls = [
            'http://localhost/admin',
            'http://127.0.0.1:8000',
            'http://0.0.0.0:3306',
            'http://[::1]/status',
            'http://169.254.169.254/latest/meta-data/',
            'http://10.0.0.1/internal',
            'http://192.168.1.1/router',
            'http://172.16.0.5/api',
            'file:///etc/passwd',
            'gopher://localhost:11211',
        ];

        foreach ($badUrls as $badUrl) {
            $check = $webService->validateSafeUrl($badUrl);
            $this->assertFalse($check['safe'], "URL '{$badUrl}' should have been blocked by SSRF validator.");
        }
    }

    public function test_ssrf_crawler_blocks_malicious_redirects_to_internal_targets(): void
    {
        $webService = app(WebResearchService::class);

        // Mock an external server returning a 302 redirect to AWS metadata
        Http::fake([
            'https://external-safe-looking-domain.com/feed' => Http::response('', 302, [
                'Location' => 'http://169.254.169.254/latest/meta-data/',
            ]),
        ]);

        $result = $webService->crawlUrl('https://external-safe-looking-domain.com/feed');

        $this->assertFalse($result['success']);
        $this->assertEquals('failed', $result['status']);
        $this->assertStringContainsString('SSRF Violation', $result['error']);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Cross-User Data Isolation (IDOR Prevention)
    |--------------------------------------------------------------------------
    */

    public function test_attacker_cannot_view_or_modify_victim_business(): void
    {
        $this->actingAs($this->attacker);

        // View
        $this->get(route('businesses.show', $this->victimBusiness))->assertStatus(403);

        // Edit form
        $this->get(route('businesses.edit', $this->victimBusiness))->assertStatus(403);

        // Update
        $this->put(route('businesses.update', $this->victimBusiness), [
            'name' => 'Hacked Enterprise',
        ])->assertStatus(403);

        // Delete
        $this->delete(route('businesses.destroy', $this->victimBusiness))->assertStatus(403);
    }

    public function test_attacker_cannot_access_or_modify_victim_website(): void
    {
        $this->actingAs($this->attacker);

        // Editor
        $this->get(route('websites.edit', $this->victimWebsite))->assertStatus(403);

        // Preview
        $this->get(route('websites.preview', $this->victimWebsite))->assertStatus(403);

        // Design update
        $this->put(route('websites.design.update', $this->victimWebsite), [
            'design_preset' => 'bold_creative',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
        ])->assertStatus(403);

        // Publish
        $this->post(route('websites.publish', $this->victimWebsite))->assertStatus(403);

        // Unpublish
        $this->post(route('websites.unpublish', $this->victimWebsite))->assertStatus(403);
    }

    public function test_attacker_cannot_manipulate_victim_sections(): void
    {
        $this->actingAs($this->attacker);

        $section = $this->victimWebsite->sections()->first();

        // Update section
        $this->put(route('sections.update', $section), [
            'content' => ['heading' => 'Hacked'],
        ])->assertStatus(403);

        // Toggle visibility
        $this->post(route('sections.toggle-visibility', $section))->assertStatus(403);

        // Delete section
        $this->delete(route('sections.delete', $section))->assertStatus(403);
    }

    public function test_attacker_cannot_access_victim_sources_or_files(): void
    {
        $file = UploadedFile::create([
            'business_id' => $this->victimBusiness->id,
            'original_name' => 'financials.pdf',
            'file_path' => 'documents/1/secret.pdf',
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'processing_status' => 'completed',
        ]);

        $source = BusinessSource::create([
            'business_id' => $this->victimBusiness->id,
            'source_type' => 'url',
            'url' => 'https://company.com',
            'status' => 'crawled',
        ]);

        $this->actingAs($this->attacker);

        // File delete
        $this->delete(route('files.destroy', $file))->assertStatus(403);

        // Source update
        $this->put(route('sources.update', $source), [
            'source_type' => 'url',
            'url' => 'https://evil.com',
        ])->assertStatus(403);

        // Source delete
        $this->delete(route('sources.destroy', $source))->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. SQL Injection Prevention in Search Filters & Lookups
    |--------------------------------------------------------------------------
    */

    public function test_sql_injection_payloads_in_dashboard_search_execute_safely(): void
    {
        $this->actingAs($this->victim);

        $sqliPayloads = [
            "' OR '1'='1",
            "1; DROP TABLE businesses; --",
            "admin' UNION SELECT * FROM users --",
            "'; WAITFOR DELAY '0:0:5'--",
        ];

        foreach ($sqliPayloads as $payload) {
            $response = $this->get(route('dashboard', ['search' => $payload]));
            $response->assertStatus(200);
            // Confirms database tables were not dropped and query executed as parameter-bound string
            $this->assertDatabaseHas('businesses', ['id' => $this->victimBusiness->id]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 6. File Upload Validation & Prohibited Executables
    |--------------------------------------------------------------------------
    */

    public function test_file_upload_rejects_php_script_disguised_as_text(): void
    {
        $this->actingAs($this->victim);

        $fakePhp = HttpUploadedFile::fake()->create('exploit.php', 10, 'application/x-php');

        $response = $this->post(route('businesses.files.store', $this->victimBusiness), [
            'file' => $fakePhp,
        ]);

        $response->assertSessionHasErrors('file');
    }
}
