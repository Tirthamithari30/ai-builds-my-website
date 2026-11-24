<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\UploadedFile as UploadedFileModel;
use App\Models\User;
use App\Services\DocumentProcessingService;
use App\Services\WebResearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class PhaseThreeDocumentAndSourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /*
    |--------------------------------------------------------------------------
    | Document Upload & Extraction Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_upload_and_extract_text_file(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Rustic Bakehouse',
            'category' => 'cafe',
        ]);

        $txtFile = UploadedFile::fake()->createWithContent(
            'menu.txt',
            "Rustic Bakehouse Specials\nPhone: +91 98220 33445\nEmail: contact@rusticbakehouse.com\nFresh Sourdough Bread and Butter Croissants."
        );

        $response = $this->actingAs($user)->post(route('businesses.files.store', $business), [
            'file' => $txtFile,
        ]);

        $response->assertRedirect(route('businesses.sources.index', $business));
        $response->assertSessionHas('success');

        $fileRecord = UploadedFileModel::where('business_id', $business->id)->first();
        $this->assertNotNull($fileRecord);
        $this->assertEquals('menu.txt', $fileRecord->original_name);
        $this->assertEquals('txt', $fileRecord->file_type);
        $this->assertEquals('completed', $fileRecord->processing_status);
        $this->assertStringContainsString('Fresh Sourdough Bread', $fileRecord->extracted_text);

        // Verify heuristic facts were extracted from the document
        $this->assertDatabaseHas('extracted_facts', [
            'business_id' => $business->id,
            'fact_key' => 'phone',
            'fact_value' => '+91 98220 33445',
        ]);
        $this->assertDatabaseHas('extracted_facts', [
            'business_id' => $business->id,
            'fact_key' => 'email',
            'fact_value' => 'contact@rusticbakehouse.com',
        ]);
    }

    public function test_user_can_upload_and_extract_docx_file(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Elite Consultants',
            'category' => 'consultant',
        ]);

        // Build valid in-memory DOCX ZIP
        $zipPath = tempnam(sys_get_temp_dir(), 'test_docx_');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString(
            'word/document.xml',
            '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Strategic Financial Consulting and Tax Advisory. Phone: +91 98111 22334</w:t></w:r></w:p></w:body></w:document>'
        );
        $zip->close();

        $docxFile = new UploadedFile(
            $zipPath,
            'company_profile.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );

        $response = $this->actingAs($user)->post(route('businesses.files.store', $business), [
            'file' => $docxFile,
        ]);

        $response->assertRedirect(route('businesses.sources.index', $business));
        $fileRecord = UploadedFileModel::where('original_name', 'company_profile.docx')->first();
        $this->assertNotNull($fileRecord);
        $this->assertEquals('docx', $fileRecord->file_type);
        $this->assertEquals('completed', $fileRecord->processing_status);
        $this->assertStringContainsString('Strategic Financial Consulting', $fileRecord->extracted_text);

        @unlink($zipPath);
    }

    public function test_image_upload_stores_asset_without_false_ocr_claims(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Artisan Roasters',
            'category' => 'cafe',
        ]);

        $image = UploadedFile::fake()->image('coffee_bar.jpg', 600, 400);

        $response = $this->actingAs($user)->post(route('businesses.files.store', $business), [
            'file' => $image,
        ]);

        $response->assertRedirect(route('businesses.sources.index', $business));
        $fileRecord = UploadedFileModel::where('original_name', 'coffee_bar.jpg')->first();
        $this->assertNotNull($fileRecord);
        $this->assertEquals('image', $fileRecord->file_type);
        $this->assertEquals('completed', $fileRecord->processing_status);
        $this->assertFalse($fileRecord->metadata['ocr_available']);
        $this->assertStringContainsString('OCR is not configured', $fileRecord->metadata['ocr_note']);
    }

    public function test_unsupported_file_types_are_rejected(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Demo Co']);

        $exeFile = UploadedFile::fake()->create('malicious.exe', 100);

        $response = $this->actingAs($user)->post(route('businesses.files.store', $business), [
            'file' => $exeFile,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('uploaded_files', ['original_name' => 'malicious.exe']);
    }

    public function test_file_size_limit_is_enforced(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Demo Co']);

        // Exceeds 15MB limit (16MB = 16384 KB)
        $hugeFile = UploadedFile::fake()->create('large.pdf', 17000);

        $response = $this->actingAs($user)->post(route('businesses.files.store', $business), [
            'file' => $hugeFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_user_can_delete_uploaded_file(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Demo Co']);

        $file = UploadedFileModel::create([
            'business_id' => $business->id,
            'original_name' => 'todelete.txt',
            'file_path' => "documents/{$business->id}/todelete.txt",
            'file_type' => 'txt',
            'mime_type' => 'text/plain',
            'file_size' => 1024,
            'processing_status' => 'completed',
        ]);

        Storage::disk('public')->put($file->file_path, 'sample content');

        $response = $this->actingAs($user)->delete(route('files.destroy', $file));

        $response->assertRedirect(route('businesses.sources.index', $business));
        $this->assertDatabaseMissing('uploaded_files', ['id' => $file->id]);
        Storage::disk('public')->assertMissing($file->file_path);
    }

    /*
    |--------------------------------------------------------------------------
    | Source Management & Web Scraping Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_add_and_crawl_valid_webpage_source(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Organic Fresh']);

        // Mock safe external HTTP response
        Http::fake([
            'https://organicfreshmarket.com*' => Http::response(
                '<!DOCTYPE html><html><head><title>Organic Fresh Market</title><meta name="description" content="Local organic produce."></head><body><h1>Welcome to Organic Fresh</h1><p>We are located in Kolhapur. Contact us at +91 98220 55667 or order@organicfresh.com.</p></body></html>',
                200
            ),
        ]);

        $response = $this->actingAs($user)->post(route('businesses.sources.store', $business), [
            'url' => 'https://organicfreshmarket.com',
            'source_type' => 'url',
        ]);

        $response->assertRedirect(route('businesses.sources.index', $business));
        $response->assertSessionHas('success');

        $source = BusinessSource::where('business_id', $business->id)->first();
        $this->assertNotNull($source);
        $this->assertEquals('crawled', $source->status);
        $this->assertEquals('Organic Fresh Market', $source->title);
        $this->assertStringContainsString('Welcome to Organic Fresh', $source->raw_content);
        $this->assertEquals('Local organic produce.', $source->metadata['meta_description']);

        // Verify phone extracted from webpage
        $this->assertDatabaseHas('extracted_facts', [
            'business_id' => $business->id,
            'fact_key' => 'phone',
            'fact_value' => '+91 98220 55667',
        ]);
    }

    public function test_source_blocked_by_platform_is_handled_gracefully(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Social Cafe']);

        // Mock 403 Forbidden on social media / anti-scraping
        Http::fake([
            'https://instagram.com/*' => Http::response('Login Required', 403),
        ]);

        $response = $this->actingAs($user)->post(route('businesses.sources.store', $business), [
            'url' => 'https://instagram.com/socialcafe',
            'source_type' => 'instagram',
        ]);

        $response->assertRedirect(route('businesses.sources.index', $business));
        $response->assertSessionHas('warning');

        $source = BusinessSource::where('url', 'https://instagram.com/socialcafe')->first();
        $this->assertNotNull($source);
        $this->assertEquals('blocked', $source->status);
        $this->assertStringContainsString('blocked automated access', $source->error_message);
    }

    public function test_ssrf_prevents_adding_internal_urls(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Test Co']);

        $maliciousUrls = [
            'http://localhost:8000/internal',
            'http://127.0.0.1:3306',
            'http://169.254.169.254/latest/meta-data',
        ];

        foreach ($maliciousUrls as $url) {
            $response = $this->actingAs($user)->post(route('businesses.sources.store', $business), [
                'url' => $url,
                'source_type' => 'url',
            ]);

            $response->assertSessionHas('error');
            $this->assertDatabaseMissing('business_sources', ['url' => $url]);
        }
    }

    public function test_user_can_update_and_retry_source(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Test Co']);

        $source = BusinessSource::create([
            'business_id' => $business->id,
            'source_type' => 'url',
            'url' => 'https://initial-site.com',
            'status' => 'failed',
        ]);

        Http::fake([
            'https://updated-site.com*' => Http::response('<html><title>Updated Site</title><body>Content</body></html>', 200),
        ]);

        $response = $this->actingAs($user)->put(route('sources.update', $source), [
            'url' => 'https://updated-site.com',
            'source_type' => 'url',
        ]);

        $response->assertRedirect(route('businesses.sources.index', $business));
        $this->assertDatabaseHas('business_sources', [
            'id' => $source->id,
            'url' => 'https://updated-site.com',
            'status' => 'crawled',
        ]);
    }

    public function test_user_can_delete_source(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Test Co']);

        $source = BusinessSource::create([
            'business_id' => $business->id,
            'source_type' => 'url',
            'url' => 'https://tobedeleted.com',
            'status' => 'crawled',
        ]);

        $response = $this->actingAs($user)->delete(route('sources.destroy', $source));

        $response->assertRedirect(route('businesses.sources.index', $business));
        $this->assertDatabaseMissing('business_sources', ['id' => $source->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cross-User Ownership Authorization Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_cannot_access_or_manage_another_users_sources(): void
    {
        $owner = User::factory()->create(['email' => 'owner@sources.com']);
        $attacker = User::factory()->create(['email' => 'attacker@sources.com']);

        $business = Business::create(['user_id' => $owner->id, 'name' => 'Owner Business']);
        $source = BusinessSource::create([
            'business_id' => $business->id,
            'source_type' => 'url',
            'url' => 'https://owner-site.com',
            'status' => 'crawled',
        ]);

        // Attacker attempts to view sources page
        $this->actingAs($attacker)->get(route('businesses.sources.index', $business))
            ->assertStatus(403);

        // Attacker attempts to add source to owner's business
        $this->actingAs($attacker)->post(route('businesses.sources.store', $business), [
            'url' => 'https://attacker.com',
            'source_type' => 'url',
        ])->assertStatus(403);

        // Attacker attempts to delete owner's source
        $this->actingAs($attacker)->delete(route('sources.destroy', $source))
            ->assertStatus(403);

        // Attacker attempts to retry owner's source
        $this->actingAs($attacker)->post(route('sources.retry', $source))
            ->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_files(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $business = Business::create(['user_id' => $owner->id, 'name' => 'Owner Business']);
        $file = UploadedFileModel::create([
            'business_id' => $business->id,
            'original_name' => 'owner_secret.pdf',
            'file_path' => "documents/{$business->id}/owner_secret.pdf",
            'file_type' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2048,
            'processing_status' => 'completed',
        ]);

        $this->actingAs($attacker)->delete(route('files.destroy', $file))
            ->assertStatus(403);

        $this->assertDatabaseHas('uploaded_files', ['id' => $file->id]);
    }
}
