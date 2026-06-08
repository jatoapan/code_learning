<?php

use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// Construimos el dataset de rutas dinámicas.
// En Pest, un dataset puede ser evaluado perezosamente, ideal para inyectar IDs de base de datos.
dataset('rutas_api', function () {
    $uuid = Str::uuid()->toString(); // Mock de UUID para rutas que requieren {id}

    return [
        // [Metodo, Ruta, HTTP_Esperado, RequiereJSON]
        'Salud del sistema' => ['GET', '/api/v1/health', 200, true],
        
        'Perfil del usuario' => ['GET', '/api/v1/user', 200, true],
        
        'Lista de instituciones' => ['GET', '/api/v1/institutions', 200, true],
        
        'Catálogo de cursos' => ['GET', '/api/v1/courses', 200, true],
        
        'Verificación de curso inexistente' => ['GET', "/api/v1/courses/{$uuid}", 404, false], // Prueba de error 404 explícito

        'Crear un reporte (Payload faltante)' => ['POST', '/api/v1/reports', 422, true], // Prueba validaciones de Requests
        
        // ** Instrucción: Debes agregar el resto de tus 120 rutas aquí siguiendo esta misma matriz **
    ];
});

test('Validación integral de Endpoints MVP', function (string $method, string $uri, int $expectedStatus, bool $checkJson) {
    // 1. Arrange: Creamos un usuario "Dios" para saltarnos los middlewares de autenticación
    $admin = User::factory()->create(['status' => 'active', 'xp' => 100]);

    // 2. Act: Ejecutamos el método correspondiente
    $response = match ($method) {
        'GET' => $this->actingAs($admin)->getJson($uri),
        'POST' => $this->actingAs($admin)->postJson($uri, []),
        'PUT' => $this->actingAs($admin)->putJson($uri, []),
        'DELETE' => $this->actingAs($admin)->deleteJson($uri),
        default => $this->actingAs($admin)->getJson($uri),
    };

    // 3. Assert: Validar el código exacto, si falla, Pest te dirá en qué URI explotó
    $response->assertStatus($expectedStatus);

    // 4. Assert: Validar estructura pura JSON si es requerido
    if ($checkJson && in_array($expectedStatus, [200, 201])) {
        // Verifica que la respuesta sea casteable como JSON
        $response->assertJson(fn ($json) => $json->hasAny());
    }
})->with('rutas_api');
