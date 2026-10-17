<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_cannot_be_accessed_without_token(): void
    {
        $this->getJson('/api/books')
            ->assertUnauthorized();
    }

    public function test_user_can_login_and_receive_token(): void
    {
        User::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'ADMIN',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.test',
            'password' => 'Password123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_authenticated_user_can_get_books_through_gateway(): void
    {
        $user = User::create([
            'name' => 'Test Librarian',
            'email' => 'librarian@example.test',
            'password' => Hash::make('Password123!'),
            'role' => 'LIBRARIAN',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/books')
            ->assertOk();
    }
}
