<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     'message',
                     'data' => ['token']
                 ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Invalid Credentials.'
                 ]);
    }

    /** @test */
    public function registration_and_creation_rules_for_users()
    {
        // 1. Cualquier persona sin autenticación puede registrarse (no admin)
        $guestResponse = $this->postJson('/api/register', [
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => true, // intenta crear admin, pero no debe poder
        ]);

        $guestResponse->assertStatus(200)
                    ->assertJsonPath('data.user.email', 'guest@example.com')
                    ->assertJsonPath('data.user.is_admin', false);

        // 2. Admin autenticado NO puede usar /register (debe fallar)
        $admin = User::factory()->create(['is_admin' => true]);
        $adminToken = $admin->createToken('Test Token')->plainTextToken;

        $adminResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
        ])->postJson('/api/register', [
            'name' => 'Should Fail - Admin',
            'email' => 'adminfail@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => true,
        ]);

        $adminResponse->assertStatus(403)
                    ->assertJson([
                        'message' => 'Authenticated users cannot register a new account.'
                    ]);

        // 3. Usuario autenticado NO admin intenta registrarse (debe fallar)
        $user = User::factory()->create(['is_admin' => false]);
        $userToken = $user->createToken('User Token')->plainTextToken;

        $userResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken,
        ])->postJson('/api/register', [
            'name' => 'Should Fail - User',
            'email' => 'userfail@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $userResponse->assertStatus(403)
                    ->assertJson([
                        'message' => 'Authenticated users cannot register a new account.'
                    ]);
    }

    /** @test */
    public function user_can_logout_himself()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
                 ->assertJson([
                     'message' => 'Logged out successfully.'
                 ]);
    }

    /** @test */
    public function admin_can_force_logout_another_user()
    {
        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $target = User::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/logout', [
            'email' => $target->email
        ]);

        $response->assertOk()
                 ->assertJson([
                     'message' => 'Logged out successfully.'
                 ]);
    }

    /** @test */
    public function user_cannot_logout_another_user()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'is_admin' => false
        ]);

        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout', [
            'email' => $otherUser->email,
            'password' => 'password'
        ]);

        $response->assertStatus(403);
    }
}