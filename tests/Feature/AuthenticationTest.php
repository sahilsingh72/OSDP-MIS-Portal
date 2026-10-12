<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::where('email', 'admin@osdp.gov.in')->first();

        $response = $this->post('/login', [
            'email' => 'admin@osdp.gov.in',
            'password' => 'Admin@12345',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login fails with invalid password.
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@osdp.gov.in',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test inactive user cannot authenticate.
     */
    public function test_inactive_user_cannot_login(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'inactive@osdp.gov.in'],
            [
                'name' => 'Inactive User',
                'password' => Hash::make('Admin@12345'),
                'is_active' => false,
            ]
        );

        $response = $this->post('/login', [
            'email' => 'inactive@osdp.gov.in',
            'password' => 'Admin@12345',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test user can logout securely.
     */
    public function test_user_can_logout_securely(): void
    {
        $user = User::where('email', 'admin@osdp.gov.in')->first();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
