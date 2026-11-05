<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void
    {
        User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => 'Password123!',
            'role' => 'ADMIN',
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.test',
            'password' => 'Password123!',
        ])->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role', 'is_active'],
            ]);
    }

    public function test_authenticated_user_can_log_out_and_revoke_their_token(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => 'Password123!',
            'role' => 'ADMIN',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout berhasil.');

        Auth::forgetGuards();

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::create([
            'name' => 'Inactive Librarian',
            'email' => 'inactive@example.test',
            'password' => 'Password123!',
            'role' => 'LIBRARIAN',
            'is_active' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => 'inactive@example.test',
            'password' => 'Password123!',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => 'Password123!',
            'role' => 'ADMIN',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'New Librarian',
                'email' => 'librarian@example.test',
                'password' => 'Password123!',
                'role' => 'LIBRARIAN',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Librarian')
            ->assertJsonPath('data.email', 'librarian@example.test')
            ->assertJsonPath('data.role', 'LIBRARIAN')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('users', [
            'email' => 'librarian@example.test',
            'role' => 'LIBRARIAN',
            'is_active' => true,
        ]);
        $this->assertTrue(
            Hash::check('Password123!', User::where('email', 'librarian@example.test')->value('password'))
        );
    }

    public function test_admin_can_reset_another_users_password_and_revoke_their_tokens(): void
    {
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => 'Password123!',
            'role' => 'ADMIN',
        ]);
        $librarian = User::create([
            'name' => 'Test Librarian',
            'email' => 'librarian@example.test',
            'password' => 'OldPassword123!',
            'role' => 'LIBRARIAN',
        ]);
        $librarian->createToken('existing-session');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/users/{$librarian->id}/reset-password", [
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Password berhasil direset');

        $this->assertTrue(Hash::check('NewPassword123!', $librarian->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->postJson('/api/login', [
            'email' => 'librarian@example.test',
            'password' => 'NewPassword123!',
        ])->assertOk();
    }

    public function test_admin_can_change_and_restore_user_status(): void
    {
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => 'Password123!',
            'role' => 'ADMIN',
        ]);
        $librarian = User::create([
            'name' => 'Test Librarian',
            'email' => 'librarian@example.test',
            'password' => 'Password123!',
            'role' => 'LIBRARIAN',
        ]);
        $librarian->createToken('existing-session');

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/users/{$librarian->id}/status", [
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', [
            'id' => $librarian->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->postJson('/api/login', [
            'email' => 'librarian@example.test',
            'password' => 'Password123!',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/users/{$librarian->id}/status", [
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->postJson('/api/login', [
            'email' => 'librarian@example.test',
            'password' => 'Password123!',
        ])->assertOk();
    }
}
