<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RouteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Generamos los roles necesarios para la prueba
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'admin']);
    }

    /**
     * Prueba de Seguridad: Un usuario sin JWT no puede acceder al perfil
     */
    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/user');
        
        // Verifica que JWT bloquea el paso (401 Unauthorized)
        $response->assertStatus(401);
    }

    /**
     * Prueba de Seguridad: Un estudiante no puede acceder a los logs de administrador
     */
    public function test_student_cannot_access_admin_routes()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        // Simulamos petición con JWT de estudiante
        $response = $this->actingAs($student, 'api')->getJson('/api/v1/admin/logs');
        
        // Verifica que Spatie RBAC bloquea el paso (403 Forbidden)
        $response->assertStatus(403);
    }

    /**
     * Prueba de Seguridad: Un administrador sí tiene permisos a sus rutas
     */
    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Simulamos petición con JWT de administrador
        $response = $this->actingAs($admin, 'api')->getJson('/api/v1/admin/logs');
        
        // Verifica que NO haya error de permisos (No 401 ni 403)
        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(401, $response->status());
    }
}
