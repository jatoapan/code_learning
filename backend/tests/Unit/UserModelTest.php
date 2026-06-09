<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba Unitaria: Verifica que el modelo User genera un UUID válido
     */
    public function test_user_has_uuid_as_primary_key()
    {
        $user = User::factory()->create();
        
        $uuidStr = (string) $user->id;
        $this->assertIsString($uuidStr);
        $this->assertEquals(36, strlen($uuidStr)); // UUID v4 length
    }

    /**
     * Prueba Unitaria: Verifica que los atributos fillable asignen correctamente
     */
    public function test_user_has_fillable_attributes()
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'status' => 'active',
            'xp' => 100
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@test.com', $user->email);
        $this->assertEquals(100, $user->xp);
    }
}
