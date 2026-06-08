<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Institution;
use App\Models\Challenge;
use App\Models\Module;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Autodetección y validación masiva de todos los endpoints de la API', function () {
    // 1. Preparar la Base de Datos y extraer UUIDs reales para evitar errores 404
    $this->artisan('db:seed'); // Carga la data del DatabaseSeeder
    
    $admin = User::where('email', 'admin@prolecom.com')->first();
    $courseId = Course::first()->id ?? Str::uuid()->toString();
    $moduleId = Module::first()->id ?? Str::uuid()->toString();
    $challengeId = Challenge::first()->id ?? Str::uuid()->toString();
    $userId = $admin->id;

    // 2. Extraer todas las rutas registradas en la aplicación
    $routes = Route::getRoutes();
    $failedRoutes = [];

    // 3. Iterar y disparar contra los 120 endpoints
    foreach ($routes as $route) {
        $uri = $route->uri();
        
        // Solo auditar rutas de la API V1
        if (!str_starts_with($uri, 'api/v1')) continue;

        $method = $route->methods()[0];
        
        // Smart Reemplazo de variables dinámicas por IDs reales
        $uriWithId = str_replace('{id}', $courseId, $uri); // Por simplicidad probamos con el ID del curso
        $uriWithId = str_replace('{user_id}', $userId, $uriWithId);
        $uriWithId = preg_replace('/\{[a-zA-Z0-9_]+\}/', $courseId, $uriWithId);

        // Disparo de la petición
        $response = match ($method) {
            'GET', 'HEAD' => $this->actingAs($admin)->getJson($uriWithId),
            'POST' => $this->actingAs($admin)->postJson($uriWithId, []),
            'PUT', 'PATCH' => $this->actingAs($admin)->putJson($uriWithId, []),
            'DELETE' => $this->actingAs($admin)->deleteJson($uriWithId),
            default => null,
        };

        if (!$response) continue;

        $status = $response->status();

        // 4. Lógica de Aserción Estricta
        // Se considera FAIL: 500 (Crash Interno) y 404 (Ruta Inexistente)
        // Se considera PASS: 200, 201 (Éxito) y 422, 403 (Validaciones Correctas de Seguridad y Formularios)
        if (in_array($status, [500, 404])) {
            $failedRoutes[] = "❌ FAIL: [$method] /$uriWithId => HTTP $status\n" . 
                              "   => Body: " . Str::limit($response->getContent(), 150);
        }
    }

    // El test explotará y detendrá el CI/CD de Railway si hay aunque sea un fallo crítico (500 o 404)
    $this->assertEmpty($failedRoutes, "CRASH DETECTADO EN LA API:\n" . implode("\n\n", $failedRoutes));
});
