<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseTwoAuthenticationAndBusinessTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Authentication Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_view_registration_form(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Create your account');
    }

    public function test_user_can_register_with_valid_information(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Founder',
            'email' => 'jane@example.com',
        ]);

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));
    }

    public function test_registration_fails_with_invalid_or_duplicate_data(): void
    {
        // 1. Password confirmation mismatch
        $res1 = $this->post(route('register'), [
            'name' => 'Jane Founder',
            'email' => 'jane@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass',
        ]);
        $res1->assertSessionHasErrors('password');

        // 2. Short password (< 8 chars)
        $res2 = $this->post(route('register'), [
            'name' => 'Jane Founder',
            'email' => 'jane2@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);
        $res2->assertSessionHasErrors('password');

        // 3. Duplicate email
        User::factory()->create(['email' => 'existing@example.com']);
        $res3 = $this->post(route('register'), [
            'name' => 'Another Person',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);
        $res3->assertSessionHasErrors('email');
    }

    public function test_user_can_view_login_form(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Sign in to your account');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => Hash::make('MyPassword123!'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'loginuser@example.com',
            'password' => 'MyPassword123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'loginuser@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'loginuser@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_is_redirected_to_login_from_protected_routes(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('businesses.create'))->assertRedirect(route('login'));
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard & Metrics Tests
    |--------------------------------------------------------------------------
    */

    public function test_dashboard_displays_real_database_counts(): void
    {
        $user = User::factory()->create(['name' => 'Alice']);

        Business::create([
            'user_id' => $user->id,
            'name' => 'Alice Cafe 1',
            'category' => 'cafe',
        ]);

        Business::create([
            'user_id' => $user->id,
            'name' => 'Alice Cafe 2',
            'category' => 'cafe',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Alice');
        $response->assertSee('Alice Cafe 1');
        $response->assertSee('Alice Cafe 2');
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total_businesses'] === 2 &&
                   $stats['published_websites'] === 0 &&
                   $stats['draft_websites'] === 0;
        });
    }

    public function test_dashboard_search_and_category_filters(): void
    {
        $user = User::factory()->create();

        Business::create([
            'user_id' => $user->id,
            'name' => 'Bombay Spices',
            'category' => 'restaurant',
            'city' => 'Mumbai',
        ]);

        Business::create([
            'user_id' => $user->id,
            'name' => 'Delhi Roasters',
            'category' => 'cafe',
            'city' => 'Delhi',
        ]);

        // Search for "Bombay"
        $res1 = $this->actingAs($user)->get(route('dashboard', ['search' => 'Bombay']));
        $res1->assertSee('Bombay Spices');
        $res1->assertDontSee('Delhi Roasters');

        // Filter by category "cafe"
        $res2 = $this->actingAs($user)->get(route('dashboard', ['category' => 'cafe']));
        $res2->assertSee('Delhi Roasters');
        $res2->assertDontSee('Bombay Spices');
    }

    /*
    |--------------------------------------------------------------------------
    | Business CRUD Operations Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_create_business_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('businesses.store'), [
            'name' => 'Royal Heritage Salon',
            'category' => 'salon',
            'phone' => '+91 98220 99887',
            'email' => 'info@royalheritage.com',
            'city' => 'Kolhapur',
            'state' => 'Maharashtra',
            'country' => 'India',
            'address' => 'Station Road, Tarabai Park',
            'description' => 'Luxury grooming and salon services for men and women.',
            'operating_hours' => 'Mon - Sun: 9 AM - 9 PM',
            'services' => "Hair Styling\nBeard Grooming\nBridal Makeup",
            'products' => "Organic Argan Oil\nMatte Pomade",
            'additional_info' => 'Free parking available in basement.',
        ]);

        $business = Business::where('name', 'Royal Heritage Salon')->first();
        $this->assertNotNull($business);
        $this->assertEquals($user->id, $business->user_id);
        $this->assertEquals('Kolhapur', $business->city);
        $this->assertEquals(['Hair Styling', 'Beard Grooming', 'Bridal Makeup'], $business->services);
        $this->assertEquals(['Organic Argan Oil', 'Matte Pomade'], $business->products);

        $response->assertRedirect(route('businesses.show', $business));
        $response->assertSessionHas('success');
    }

    public function test_business_creation_validates_required_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('businesses.store'), [
            'name' => '', // Empty required name
            'city' => 'Kolhapur',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('businesses', ['city' => 'Kolhapur']);
    }

    public function test_user_can_view_their_business(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Coastal Seafood Diner',
            'category' => 'restaurant',
            'city' => 'Goa',
            'description' => 'Fresh catch of the day served coastal style.',
        ]);

        $response = $this->actingAs($user)->get(route('businesses.show', $business));

        $response->assertStatus(200);
        $response->assertSee('Coastal Seafood Diner');
        $response->assertSee('Fresh catch of the day served coastal style.');
    }

    public function test_user_can_update_their_business(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Original Name',
            'category' => 'retail',
            'city' => 'Nagpur',
        ]);

        $response = $this->actingAs($user)->put(route('businesses.update', $business), [
            'name' => 'Updated Store Name',
            'category' => 'retail',
            'city' => 'Nagpur City',
            'phone' => '+91 91234 56789',
            'description' => 'Updated retail offerings.',
        ]);

        $response->assertRedirect(route('businesses.show', $business));
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'Updated Store Name',
            'city' => 'Nagpur City',
            'phone' => '+91 91234 56789',
        ]);
    }

    public function test_user_can_delete_their_business(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Business To Delete',
            'category' => 'other',
        ]);

        $response = $this->actingAs($user)->delete(route('businesses.destroy', $business));

        $response->assertRedirect(route('dashboard'));
        $this->assertSoftDeleted('businesses', ['id' => $business->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Cross-User Data Isolation & Ownership Authorization Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_cannot_view_another_users_business(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $attacker = User::factory()->create(['email' => 'attacker@example.com']);

        $secretBusiness = Business::create([
            'user_id' => $owner->id,
            'name' => "Owner's Confidential Business",
            'category' => 'consultant',
        ]);

        $response = $this->actingAs($attacker)->get(route('businesses.show', $secretBusiness));

        // Must be forbidden (HTTP 403)
        $response->assertStatus(403);
    }

    public function test_user_cannot_update_another_users_business(): void
    {
        $owner = User::factory()->create(['email' => 'owner2@example.com']);
        $attacker = User::factory()->create(['email' => 'attacker2@example.com']);

        $business = Business::create([
            'user_id' => $owner->id,
            'name' => "Owner's Original Name",
            'category' => 'retail',
        ]);

        $response = $this->actingAs($attacker)->put(route('businesses.update', $business), [
            'name' => 'Hacked Business Name',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => "Owner's Original Name",
        ]);
    }

    public function test_user_cannot_delete_another_users_business(): void
    {
        $owner = User::factory()->create(['email' => 'owner3@example.com']);
        $attacker = User::factory()->create(['email' => 'attacker3@example.com']);

        $business = Business::create([
            'user_id' => $owner->id,
            'name' => "Owner's Protected Business",
            'category' => 'retail',
        ]);

        $response = $this->actingAs($attacker)->delete(route('businesses.destroy', $business));

        $response->assertStatus(403);
        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'deleted_at' => null,
        ]);
    }
}
