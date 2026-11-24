<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Models\Website;
use App\Models\WebsiteSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFourteenUiUxImprovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_password_visibility_toggle(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('showPassword: false', false);
        $response->assertSee('data-testid="toggle-password"', false);
        $response->assertSee(':type="showPassword ? \'text\' : \'password\'"', false);
        $response->assertSee('Toggle password visibility');
    }

    public function test_register_page_renders_password_and_confirmation_toggles(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('showPassword: false', false);
        $response->assertSee('showConfirmPassword: false', false);
        $response->assertSee('data-testid="toggle-register-password"', false);
        $response->assertSee('data-testid="toggle-register-confirm-password"', false);
    }

    public function test_authenticated_app_layout_renders_mobile_drawer_and_avatar_badge(): void
    {
        $user = User::factory()->create([
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('data-testid="mobile-menu-toggle"', false);
        $response->assertSee('id="mobile-navigation-drawer"', false);
        $response->assertSee('Sarah Connor');
        $response->assertSee(strtoupper(substr($user->name, 0, 1)));
    }

    public function test_flash_notifications_contain_alpine_auto_dismiss_directives(): void
    {
        $user = User::factory()->create();

        // Success flash alert
        $responseSuccess = $this->actingAs($user)
            ->withSession(['success' => 'Website published successfully!'])
            ->get(route('dashboard'));

        $responseSuccess->assertStatus(200);
        $responseSuccess->assertSee('data-testid="flash-success"', false);
        $responseSuccess->assertSee('Website published successfully!');
        $responseSuccess->assertSee('setTimeout(() => show = false, 5000)', false);
        $responseSuccess->assertSee('Dismiss notification');

        // Error flash alert
        $responseError = $this->actingAs($user)
            ->withSession(['error' => 'Conflict resolution required before generation.'])
            ->get(route('dashboard'));

        $responseError->assertStatus(200);
        $responseError->assertSee('data-testid="flash-error"', false);
        $responseError->assertSee('Conflict resolution required before generation.');
        $responseError->assertSee('setTimeout(() => show = false, 7000)', false);
        $responseError->assertSee('Dismiss error');
    }

    public function test_welcome_page_renders_mobile_drawer_toggle(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('data-testid="welcome-mobile-toggle"', false);
        $response->assertSee('id="welcome-mobile-drawer"', false);
        $response->assertSee('Architecture');
        $response->assertSee('Services');
    }

    public function test_dashboard_renders_enhanced_metric_cards_and_icons(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Saved Projects');
        $response->assertSee('Published Websites');
        $response->assertSee('Draft Websites');
        $response->assertSee('Extracted Facts');
    }

    public function test_website_setup_page_renders_generating_overlay_and_submit_state(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Acme Consulting',
            'category' => 'consultant',
            'phone' => '1234567890',
            'email' => 'info@acme.com',
            'city' => 'New York',
        ]);

        $response = $this->actingAs($user)->get(route('businesses.website.setup', $business));

        $response->assertStatus(200);
        $response->assertSee('data-testid="generating-overlay"', false);
        $response->assertSee('Generating Your Website');
        $response->assertSee('data-testid="generate-website-submit-btn"', false);
        $response->assertSee('generating: false', false);
        $response->assertSee('@submit="generating = true"', false);
    }

    public function test_website_editor_renders_enhanced_save_status_indicator(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Acme Consulting',
            'category' => 'consultant',
            'phone' => '1234567890',
            'email' => 'info@acme.com',
            'city' => 'New York',
        ]);
        $website = Website::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'title' => 'Test Business Website',
            'theme' => 'modern_minimal',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
            'current_version' => 1,
            'is_published' => false,
            'slug' => 'test-business-website',
        ]);

        WebsiteSection::create([
            'website_id' => $website->id,
            'section_type' => 'hero',
            'order' => 1,
            'title' => 'Hero Banner',
            'content' => [
                'heading' => 'Welcome to Test Business',
                'subheading' => 'Quality services',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('websites.edit', $website));

        $response->assertStatus(200);
        $response->assertSee('Saving changes...');
        $response->assertSee('saveStatus');
        $response->assertSee('All changes saved');
    }
}
