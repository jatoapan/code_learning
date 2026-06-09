<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'student']);
    }

    /**
     * Sprint 1: Registro con email duplicado debe retornar 422
     */
    public function test_registration_with_duplicate_email_returns_422()
    {
        User::factory()->create(['email' => 'duplicado@test.com']);

        $response = $this->postJson('/api/v1/users', [
            'name'                  => 'Otro Usuario',
            'email'                 => 'duplicado@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Sprint 1: Login con contraseña incorrecta debe retornar 401
     */
    public function test_login_with_wrong_password_returns_401()
    {
        User::factory()->create([
            'email'    => 'usuario@test.com',
            'password' => bcrypt('password_correcta'),
        ]);

        $response = $this->postJson('/api/v1/sessions', [
            'email'       => 'usuario@test.com',
            'password'    => 'password_incorrecta',
            'device_name' => 'test',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Sprint 1: Token invalidado después de logout no puede ser reutilizado
     */
    public function test_token_is_invalidated_after_logout()
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        // Login → obtenemos token
        $loginResponse = $this->postJson('/api/v1/sessions', [
            'email'       => $user->email,
            'password'    => 'password',
            'device_name' => 'test',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');

        // Logout → token queda invalidado
        $this->withToken($token)->deleteJson('/api/v1/sessions/current')->assertStatus(200);

        // Intento de usar el token después del logout → debe ser bloqueado (401)
        $response = $this->withToken($token)->getJson('/api/v1/user');
        $response->assertStatus(401);
    }

    /**
     * Sprint 1: Usuario no autenticado no puede acceder a rutas protegidas
     */
    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $this->getJson('/api/v1/user')->assertStatus(401);
    }
}
