<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Dataset con la matriz de endpoints a probar dinámicamente
dataset('endpoints_basicos', [
    ['GET', '/api/v1/health', 200, ['status']],
    ['GET', '/api/v1/user', 200, ['id', 'name', 'email']],
    ['GET', '/api/v1/courses', 200, []], // Asumiendo que devuelve un array o paginador
    // Agrega aquí los 120 endpoints con sus payloads si son POST/PUT
]);

test('los endpoints responden correctamente', function (string $method, string $uri, int $expectedStatus, array $expectedJsonStructure) {
    // 1. Arrange: Autenticamos un usuario de prueba dinámicamente
    $user = User::factory()->create();
    
    // 2. Act: Disparamos la petición HTTP
    $response = match ($method) {
        'GET' => $this->actingAs($user)->getJson($uri),
        'POST' => $this->actingAs($user)->postJson($uri, []), // Payload vacío por defecto
        'DELETE' => $this->actingAs($user)->deleteJson($uri),
        default => $this->actingAs($user)->getJson($uri),
    };

    // 3. Assert: Validamos Código de Estado y Estructura
    $response->assertStatus($expectedStatus);
    
    if (!empty($expectedJsonStructure)) {
        $response->assertJsonStructure($expectedJsonStructure);
    }
})->with('endpoints_basicos');
