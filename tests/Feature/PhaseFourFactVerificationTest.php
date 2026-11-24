<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use App\Models\UploadedFile as UploadedFileModel;
use App\Models\User;
use App\Services\AiService;
use App\Services\FactVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhaseFourFactVerificationTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Fact Review Interface & Access Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_view_fact_verification_screen(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Kop Coffee Works',
            'category' => 'cafe',
            'city' => 'Kolhapur',
        ]);

        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'source_url' => 'Brochure.pdf',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 77889',
            'evidence' => 'Contact our Kolhapur branch: +91 98220 77889',
            'verification_status' => 'extracted',
            'confidence_score' => 0.92,
        ]);

        $response = $this->actingAs($user)->get(route('businesses.facts.index', $business));

        $response->assertStatus(200);
        $response->assertSee('Fact Extraction');
        $response->assertSee('+91 98220 77889');
        $response->assertSee('Brochure.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Fact Actions: Confirm, Reject, Edit, Outdated
    |--------------------------------------------------------------------------
    */

    public function test_user_can_confirm_an_extracted_fact(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Demo Cafe']);

        $fact = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'source_url' => 'Menu.pdf',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 12345',
            'verification_status' => 'extracted',
            'is_confirmed' => false,
        ]);

        $response = $this->actingAs($user)->post(route('facts.confirm', $fact));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fact->refresh();
        $this->assertTrue($fact->is_confirmed);
        $this->assertEquals('user-confirmed', $fact->verification_status);

        // Audit confirmation record created
        $this->assertDatabaseHas('fact_confirmations', [
            'business_id' => $business->id,
            'fact_key' => 'phone',
            'confirmed_value' => '+91 98220 12345',
            'resolution_type' => 'accepted',
            'confirmed_by' => $user->id,
        ]);
    }

    public function test_user_can_edit_and_confirm_fact_value(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Demo Salon']);

        $fact = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'webpage',
            'source_url' => 'https://salon.com',
            'fact_key' => 'address',
            'fact_name' => 'Physical Address',
            'fact_value' => 'Old Address 123',
            'verification_status' => 'extracted',
        ]);

        $response = $this->actingAs($user)->put(route('facts.edit-confirm', $fact), [
            'fact_value' => 'Correct Suite 404, Business Bay, Pune',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fact->refresh();
        $this->assertEquals('user-confirmed', $fact->verification_status);
        $this->assertEquals('Correct Suite 404, Business Bay, Pune', $fact->user_confirmed_value);

        $this->assertDatabaseHas('fact_confirmations', [
            'business_id' => $business->id,
            'fact_key' => 'address',
            'confirmed_value' => 'Correct Suite 404, Business Bay, Pune',
            'resolution_type' => 'custom_edited',
        ]);
    }

    public function test_user_can_reject_a_fact(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Demo Co']);

        $fact = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'fact_key' => 'pricing',
            'fact_name' => 'Pricing Information',
            'fact_value' => 'Outdated ₹500 Package',
            'verification_status' => 'extracted',
        ]);

        $response = $this->actingAs($user)->post(route('facts.reject', $fact), [
            'reason' => 'Package discontinued last year',
        ]);

        $response->assertRedirect();
        $fact->refresh();
        $this->assertEquals('rejected', $fact->verification_status);
        $this->assertFalse($fact->is_confirmed);
        $this->assertStringContainsString('Package discontinued last year', $fact->notes);
    }

    public function test_user_can_mark_fact_as_outdated(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Demo Co']);

        $fact = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'fact_key' => 'operating_hours',
            'fact_name' => 'Operating Hours',
            'fact_value' => '10 AM - 5 PM',
            'verification_status' => 'extracted',
        ]);

        $response = $this->actingAs($user)->post(route('facts.outdated', $fact));

        $response->assertRedirect();
        $fact->refresh();
        $this->assertEquals('outdated', $fact->verification_status);
        $this->assertFalse($fact->is_confirmed);
    }

    /*
    |--------------------------------------------------------------------------
    | Conflict Detection & Resolution Tests
    |--------------------------------------------------------------------------
    */

    public function test_conflicting_phone_numbers_and_addresses_are_detected(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Metropolis Clinic']);

        // Candidate 1: Phone A from Google listing
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'google_business',
            'source_url' => 'https://maps.google.com/clinic',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 11111',
            'evidence' => 'Google Listing Phone: +91 98220 11111',
            'verification_status' => 'extracted',
        ]);

        // Candidate 2: Phone B from Brochure PDF
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'source_url' => 'Brochure.pdf',
            'fact_key' => 'phone',
            'fact_name' => 'Phone Number',
            'fact_value' => '+91 98220 22222',
            'evidence' => 'Helpline: +91 98220 22222',
            'verification_status' => 'extracted',
        ]);

        // Candidate 3: Conflicting Address
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'webpage',
            'source_url' => 'https://clinic.com',
            'fact_key' => 'address',
            'fact_name' => 'Physical Address',
            'fact_value' => '100 North Park Way',
            'verification_status' => 'extracted',
        ]);
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'source_url' => 'Flyer.pdf',
            'fact_key' => 'address',
            'fact_name' => 'Physical Address',
            'fact_value' => '250 South Mall Road',
            'verification_status' => 'extracted',
        ]);

        $service = app(FactVerificationService::class);
        $analysis = $service->analyzeFacts($business);

        $this->assertTrue($analysis['has_blocking_conflicts']);
        $this->assertArrayHasKey('phone', $analysis['conflicts']);
        $this->assertArrayHasKey('address', $analysis['conflicts']);
        $this->assertCount(2, $analysis['conflicts']['phone']['candidates']);
        $this->assertCount(2, $analysis['conflicts']['address']['candidates']);

        // Resolve phone conflict
        $response = $this->actingAs($user)->post(route('businesses.facts.resolve-conflict', $business), [
            'fact_key' => 'phone',
            'confirmed_value' => '+91 98220 11111',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $freshAnalysis = $service->analyzeFacts($business);
        $this->assertArrayNotHasKey('phone', $freshAnalysis['conflicts']);
        $this->assertEquals('+91 98220 11111', $freshAnalysis['confirmed']['phone']['fact_value']);
    }

    public function test_conflicting_opening_hours_and_pricing_resolution(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Fit Zone']);

        // Hours conflict
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'webpage',
            'fact_key' => 'operating_hours',
            'fact_name' => 'Operating Hours',
            'fact_value' => 'Mon-Sat: 6 AM - 10 PM',
            'verification_status' => 'extracted',
        ]);
        ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'document',
            'fact_key' => 'operating_hours',
            'fact_name' => 'Operating Hours',
            'fact_value' => 'Open 24/7',
            'verification_status' => 'extracted',
        ]);

        $service = app(FactVerificationService::class);
        $analysis = $service->analyzeFacts($business);
        $this->assertArrayHasKey('operating_hours', $analysis['conflicts']);

        // Resolve conflict with custom value
        $this->actingAs($user)->post(route('businesses.facts.resolve-conflict', $business), [
            'fact_key' => 'operating_hours',
            'confirmed_value' => 'Mon - Sun: 5:30 AM - 11:00 PM',
        ]);

        $freshAnalysis = $service->analyzeFacts($business);
        $this->assertEquals('Mon - Sun: 5:30 AM - 11:00 PM', $freshAnalysis['confirmed']['operating_hours']['fact_value']);
    }

    /*
    |--------------------------------------------------------------------------
    | Manual Fact Entry & Missing Information Tests
    |--------------------------------------------------------------------------
    */

    public function test_owner_can_add_manual_fact_flagged_as_owner_provided(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Handmade Pottery']);

        $response = $this->actingAs($user)->post(route('businesses.facts.manual', $business), [
            'fact_key' => 'certifications',
            'fact_name' => 'Certifications & Accreditations',
            'fact_value' => 'Certified Master Artisan by National Crafts Council',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $fact = ExtractedFact::where('business_id', $business->id)->where('fact_key', 'certifications')->first();
        $this->assertNotNull($fact);
        $this->assertTrue($fact->is_owner_provided);
        $this->assertTrue($fact->is_confirmed);
        $this->assertEquals('user-confirmed', $fact->verification_status);
        $this->assertEquals('manual', $fact->source_type);
    }

    /*
    |--------------------------------------------------------------------------
    | AI Extraction, Response Validation & Duplicate Avoidance
    |--------------------------------------------------------------------------
    */

    public function test_ai_unavailable_behavior_falls_back_without_fabrication(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Local Books']);

        UploadedFileModel::create([
            'business_id' => $business->id,
            'original_name' => 'contact.txt',
            'file_path' => "documents/{$business->id}/contact.txt",
            'file_type' => 'txt',
            'mime_type' => 'text/plain',
            'file_size' => 100,
            'extracted_text' => 'Local Books Store. Phone: +91 98220 88990. Email: books@local.com',
            'processing_status' => 'completed',
        ]);

        $service = app(FactVerificationService::class);
        $result = $service->extractAllSources($business);

        $this->assertTrue($result['success']);
        // Verify regex heuristics extracted real facts without AI inventing fake data
        $this->assertDatabaseHas('extracted_facts', [
            'business_id' => $business->id,
            'fact_key' => 'phone',
            'fact_value' => '+91 98220 88990',
        ]);
        $this->assertDatabaseHas('extracted_facts', [
            'business_id' => $business->id,
            'fact_key' => 'email',
            'fact_value' => 'books@local.com',
        ]);
    }

    public function test_reextract_does_not_create_duplicate_identical_facts(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['user_id' => $user->id, 'name' => 'Local Books']);

        UploadedFileModel::create([
            'business_id' => $business->id,
            'original_name' => 'contact.txt',
            'file_path' => "documents/{$business->id}/contact.txt",
            'file_type' => 'txt',
            'mime_type' => 'text/plain',
            'file_size' => 100,
            'extracted_text' => 'Local Books Store. Phone: +91 98220 88990.',
            'processing_status' => 'completed',
        ]);

        $service = app(FactVerificationService::class);

        // Run 1
        $service->extractAllSources($business);
        $count1 = ExtractedFact::where('business_id', $business->id)->where('fact_key', 'phone')->count();

        // Run 2 (Idempotent retry)
        $service->extractAllSources($business);
        $count2 = ExtractedFact::where('business_id', $business->id)->where('fact_key', 'phone')->count();

        $this->assertEquals(1, $count1);
        $this->assertEquals(1, $count2);
    }

    /*
    |--------------------------------------------------------------------------
    | Cross-User Access Protection Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_cannot_access_or_verify_another_users_facts(): void
    {
        $owner = User::factory()->create(['email' => 'owner@facts.com']);
        $attacker = User::factory()->create(['email' => 'attacker@facts.com']);

        $business = Business::create(['user_id' => $owner->id, 'name' => 'Owner Private Biz']);
        $fact = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'manual',
            'fact_key' => 'phone',
            'fact_name' => 'Phone',
            'fact_value' => '+91 98220 00000',
            'verification_status' => 'extracted',
        ]);

        // Attacker views facts screen
        $this->actingAs($attacker)->get(route('businesses.facts.index', $business))
            ->assertStatus(403);

        // Attacker confirms owner's fact
        $this->actingAs($attacker)->post(route('facts.confirm', $fact))
            ->assertStatus(403);

        // Attacker edits owner's fact
        $this->actingAs($attacker)->put(route('facts.edit-confirm', $fact), ['fact_value' => 'Hacked'])
            ->assertStatus(403);

        // Attacker rejects owner's fact
        $this->actingAs($attacker)->post(route('facts.reject', $fact))
            ->assertStatus(403);

        // Attacker marks owner's fact outdated
        $this->actingAs($attacker)->post(route('facts.outdated', $fact))
            ->assertStatus(403);

        // Attacker resolves conflict on owner's business
        $this->actingAs($attacker)->post(route('businesses.facts.resolve-conflict', $business), [
            'fact_key' => 'phone',
            'confirmed_value' => '999',
        ])->assertStatus(403);

        // Attacker adds manual fact to owner's business
        $this->actingAs($attacker)->post(route('businesses.facts.manual', $business), [
            'fact_key' => 'phone',
            'fact_name' => 'Phone',
            'fact_value' => '999',
        ])->assertStatus(403);
    }
}
