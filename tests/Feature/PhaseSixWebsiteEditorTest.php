<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use App\Models\WebsiteVersion;
use App\Services\WebsiteGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PhaseSixWebsiteEditorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Business $business;
    protected Website $website;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Vikram Patil',
            'email' => 'vikram@kolhapurfood.com',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Sneha Kulkarni',
            'email' => 'sneha@kulkarnilaw.com',
        ]);

        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Patil Traditional Spices & Foods',
            'category' => 'retail',
            'description' => 'Authentic Kolhapuri masalas and artisanal spice blends crafted since 1985.',
            'city' => 'Kolhapur',
            'state' => 'Maharashtra',
            'country' => 'India',
            'address' => '45 Shahupuri 2nd Lane, Kolhapur',
            'phone' => '+91 94220 54321',
            'email' => 'orders@patilspices.com',
            'operating_hours' => ['raw' => 'Mon - Sat: 10:00 AM - 9:00 PM'],
            'products' => ['Kolhapuri Kanda Lasun Masala', 'Special Mutton Masala', 'Goda Masala'],
        ]);

        // Generate baseline website for testing editor features
        $service = app(WebsiteGenerationService::class);
        $this->website = $service->generateWebsite($this->business, [
            'title' => 'Patil Traditional Spices Official',
            'design_preset' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'sections' => ['hero', 'about', 'products', 'contact', 'footer'],
        ]);
    }

    public function test_owner_can_access_website_editor(): void
    {
        $response = $this->actingAs($this->user)->get(route('websites.edit', $this->website));

        $response->assertStatus(200);
        $response->assertSee('Patil Traditional Spices Official');
        $response->assertSee('Editor v');
        $response->assertSee('Hero Banner');
        $response->assertSee('About Us');
        $response->assertSee('Design');
        $response->assertSee('Images');
    }

    public function test_owner_can_update_section_content(): void
    {
        $heroSec = $this->website->sections()->where('section_type', 'hero')->firstOrFail();

        $updatedContent = $heroSec->content;
        $updatedContent['heading'] = 'Finest Artisanal Spices of Kolhapur';
        $updatedContent['subheading'] = 'Handcrafted spice blends honoring authentic traditional recipes.';

        $response = $this->actingAs($this->user)
            ->put(route('sections.update', $heroSec), [
                'content' => $updatedContent,
                'is_visible' => true,
            ]);

        $response->assertRedirect(route('websites.edit', $this->website));

        $refreshed = $heroSec->fresh();
        $this->assertEquals('Finest Artisanal Spices of Kolhapur', $refreshed->content['heading']);
        $this->assertEquals('Handcrafted spice blends honoring authentic traditional recipes.', $refreshed->content['subheading']);
        $this->assertTrue($refreshed->design_overrides['is_manually_edited'] ?? false);
    }

    public function test_owner_can_add_supported_section(): void
    {
        $initialCount = $this->website->sections()->count();

        $response = $this->actingAs($this->user)
            ->post(route('websites.sections.add', $this->website), [
                'section_type' => 'testimonials',
            ]);

        $response->assertRedirect(route('websites.edit', $this->website));

        $this->assertEquals($initialCount + 1, $this->website->sections()->count());
        $newSec = $this->website->sections()->where('section_type', 'testimonials')->first();
        $this->assertNotNull($newSec);
        $this->assertTrue($newSec->is_visible);

        // Snapshot recorded
        $this->assertDatabaseHas('website_versions', [
            'website_id' => $this->website->id,
            'changelog' => 'Added Client Testimonials section',
        ]);
    }

    public function test_owner_can_delete_section(): void
    {
        $initialCount = $this->website->sections()->count();
        $aboutSec = $this->website->sections()->where('section_type', 'about')->firstOrFail();

        $response = $this->actingAs($this->user)
            ->delete(route('sections.delete', $aboutSec));

        $response->assertRedirect(route('websites.edit', $this->website));

        $this->assertEquals($initialCount - 1, $this->website->sections()->count());
        $this->assertDatabaseMissing('website_sections', ['id' => $aboutSec->id]);

        // Remaining sections re-indexed
        $orders = $this->website->sections()->orderBy('order')->pluck('order')->all();
        $this->assertEquals(range(0, count($orders) - 1), $orders);
    }

    public function test_owner_cannot_delete_last_remaining_section(): void
    {
        // Delete sections until only 1 remains
        $sections = $this->website->sections()->get();
        for ($i = 0; $i < count($sections) - 1; $i++) {
            $sections[$i]->delete();
        }

        $this->assertEquals(1, $this->website->sections()->count());
        $lastSec = $this->website->sections()->first();

        // Attempting to delete the last remaining section must fail
        $response = $this->actingAs($this->user)
            ->delete(route('sections.delete', $lastSec));

        $response->assertSessionHas('error');
        $this->assertEquals(1, $this->website->sections()->count());
    }

    public function test_owner_can_toggle_section_visibility(): void
    {
        $heroSec = $this->website->sections()->firstOrFail();
        $this->assertTrue($heroSec->is_visible);

        // Toggle to hidden
        $response = $this->actingAs($this->user)
            ->post(route('sections.toggle-visibility', $heroSec));

        $this->assertFalse($heroSec->fresh()->is_visible);

        // Toggle back to visible
        $response2 = $this->actingAs($this->user)
            ->post(route('sections.toggle-visibility', $heroSec));

        $this->assertTrue($heroSec->fresh()->is_visible);
    }

    public function test_owner_can_reorder_sections(): void
    {
        $sec1 = $this->website->sections()->orderBy('order', 'asc')->first();
        $sec2 = $this->website->sections()->where('order', 1)->first();

        $this->assertNotNull($sec1);
        $this->assertNotNull($sec2);
        $this->assertEquals(0, $sec1->order);
        $this->assertEquals(1, $sec2->order);

        // Move sec2 UP
        $response = $this->actingAs($this->user)
            ->post(route('websites.sections.reorder', $this->website), [
                'section_id' => $sec2->id,
                'direction' => 'up',
            ]);

        $this->assertEquals(0, $sec2->fresh()->order);
        $this->assertEquals(1, $sec1->fresh()->order);
    }

    public function test_owner_can_update_website_design_and_styling(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('websites.design.update', $this->website), [
                'title' => 'Patil Spices & Masala Mart',
                'design_preset' => 'premium_elegant',
                'primary_color' => '#b45309',
                'secondary_color' => '#1c1917',
                'font_family' => "'Playfair Display', Georgia, serif",
                'button_style' => 'pill',
                'section_spacing' => 'spacious',
            ]);

        $response->assertRedirect(route('websites.edit', $this->website));

        $refreshed = $this->website->fresh();
        $this->assertEquals('Patil Spices & Masala Mart', $refreshed->title);
        $this->assertEquals('premium_elegant', $refreshed->design_preset);
        $this->assertEquals('#b45309', $refreshed->primary_color);
        $this->assertEquals('#1c1917', $refreshed->secondary_color);
        $this->assertEquals('pill', $refreshed->settings['button_style'] ?? '');
        $this->assertEquals('spacious', $refreshed->settings['section_spacing'] ?? '');
    }

    public function test_gemini_section_regeneration_preview_workflow(): void
    {
        config(['services.gemini.api_key' => 'mock-gemini-key']);

        $heroSec = $this->website->sections()->where('section_type', 'hero')->firstOrFail();
        $originalHeading = $heroSec->content['heading'];

        $mockAiPayload = [
            'badge' => 'Verified Heritage Spices',
            'heading' => 'Authentic Maharashtrian Flavors Reimagined',
            'subheading' => 'Handcrafted Kolhapuri spice blends formulated with pure heirloom ingredients.',
            'primary_button_text' => 'Order Masalas',
            'primary_button_link' => '#contact',
            'hero_image' => 'https://images.unsplash.com/photo-spices',
        ];

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($mockAiPayload)],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Request preview only - must NOT overwrite database yet
        $response = $this->actingAs($this->user)
            ->postJson(route('sections.regenerate-preview', $heroSec), [
                'user_prompt' => 'Make the tone more premium and highlight heritage',
            ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertTrue($data['ai_generated']);
        $this->assertEquals($originalHeading, $data['original_content']['heading']);
        $this->assertEquals('Authentic Maharashtrian Flavors Reimagined', $data['proposed_content']['heading']);

        // Verify DB remains untouched
        $this->assertEquals($originalHeading, $heroSec->fresh()->content['heading']);
    }

    public function test_owner_can_accept_and_apply_regenerated_section(): void
    {
        $heroSec = $this->website->sections()->where('section_type', 'hero')->firstOrFail();
        $oldHeading = $heroSec->content['heading'];

        $approvedContent = $heroSec->content;
        $approvedContent['heading'] = 'Approved New Regenerated Heading';
        $approvedContent['subheading'] = 'Approved new subheading grounded in verified spices.';

        $response = $this->actingAs($this->user)
            ->postJson(route('sections.apply-regeneration', $heroSec), [
                'content' => $approvedContent,
            ]);

        $response->assertStatus(200);

        $refreshed = $heroSec->fresh();
        $this->assertEquals('Approved New Regenerated Heading', $refreshed->content['heading']);
        $this->assertEquals($oldHeading, $refreshed->design_overrides['previous_content']['heading']);

        // Snapshot recorded
        $this->assertDatabaseHas('website_versions', [
            'website_id' => $this->website->id,
            'changelog' => 'Regenerated hero section with owner approval',
        ]);
    }

    public function test_bulk_save_all_autosave_endpoint(): void
    {
        $heroSec = $this->website->sections()->where('section_type', 'hero')->firstOrFail();
        $contactSec = $this->website->sections()->where('section_type', 'contact')->firstOrFail();

        $updatedHero = $heroSec->content;
        $updatedHero['heading'] = 'Autosaved Hero Title';

        $updatedContact = $contactSec->content;
        $updatedContact['heading'] = 'Autosaved Contact Header';

        $response = $this->actingAs($this->user)
            ->postJson(route('websites.save-all', $this->website), [
                'title' => 'Autosaved Website Title',
                'primary_color' => '#10b981',
                'sections' => [
                    ['id' => $heroSec->id, 'content' => $updatedHero, 'is_visible' => true],
                    ['id' => $contactSec->id, 'content' => $updatedContact, 'is_visible' => true],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('Autosaved Website Title', $this->website->fresh()->title);
        $this->assertEquals('#10b981', $this->website->fresh()->primary_color);
        $this->assertEquals('Autosaved Hero Title', $heroSec->fresh()->content['heading']);
        $this->assertEquals('Autosaved Contact Header', $contactSec->fresh()->content['heading']);
    }

    public function test_cross_user_access_protection(): void
    {
        $heroSec = $this->website->sections()->firstOrFail();

        // User B cannot access editor
        $this->actingAs($this->otherUser)
            ->get(route('websites.edit', $this->website))
            ->assertStatus(403);

        // User B cannot update section
        $this->actingAs($this->otherUser)
            ->put(route('sections.update', $heroSec), ['content' => ['heading' => 'Hacked']])
            ->assertStatus(403);

        // User B cannot delete section
        $this->actingAs($this->otherUser)
            ->delete(route('sections.delete', $heroSec))
            ->assertStatus(403);

        // User B cannot reorder sections
        $this->actingAs($this->otherUser)
            ->post(route('websites.sections.reorder', $this->website), ['section_id' => $heroSec->id, 'direction' => 'up'])
            ->assertStatus(403);

        // User B cannot trigger regeneration
        $this->actingAs($this->otherUser)
            ->postJson(route('sections.regenerate-preview', $heroSec), ['user_prompt' => 'Hacked'])
            ->assertStatus(403);

        // User B cannot update design
        $this->actingAs($this->otherUser)
            ->put(route('websites.design.update', $this->website), [
                'design_preset' => 'modern_minimal',
                'primary_color' => '#2563eb',
                'secondary_color' => '#0f172a',
            ])
            ->assertStatus(403);
    }
}
