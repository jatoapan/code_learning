<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class EndpointTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function all_api_endpoints_do_not_crash(): void
    {
        // 1. Preparar entorno base (Base de datos en memoria para el test)
        $this->artisan('db:seed');
        $admin = User::where('email', 'admin@prolecom.com')->first() 
                 ?? User::factory()->create(['status' => 'active', 'xp' => 100]);

        // 2. Falsificar llamadas externas (ej. Judge0) estrictamente dentro del contexto del test
        Http::fake();

        $routes = Route::getRoutes();
        $failedRoutes = [];

        // 3. Evaluar dinámicamente
        foreach ($routes as $route) {
            $uri = $route->uri();

            // Filtrar solo las rutas bajo /api/v1
            if (!str_starts_with($uri, 'api/v1')) {
                continue;
            }

            $method = $route->methods()[0];

            // Sustitución de parámetros dinámicos ({id}, {user_id}, etc.) por valor dummy 1
            $uriWithDummy = preg_replace('/\{[a-zA-Z0-9_]+\}/', '1', $uri);

            // 4. Ejecución usando Helpers Nativos Strictos (getJson, postJson)
            $response = match ($method) {
                'GET', 'HEAD' => $this->actingAs($admin)->getJson($uriWithDummy),
                'POST' => $this->actingAs($admin)->postJson($uriWithDummy, []),
                'PUT', 'PATCH' => $this->actingAs($admin)->putJson($uriWithDummy, []),
                'DELETE' => $this->actingAs($admin)->deleteJson($uriWithDummy),
                default => null,
            };

            if (!$response) {
                continue;
            }

            // Reporte claro si el status es >= 500 (Fatal Error/Crash)
            if ($response->status() >= 500) {
                $content = substr($response->getContent(), 0, 300);
                $failedRoutes[] = "❌ FAIL: [$method] /$uriWithDummy => HTTP " . $response->status() . "\n   Detalle: " . $content;
            }
        }

        // 5. Aserción final requerida por PHPUnit 11
        $this->assertEmpty($failedRoutes, "CRASH DETECTADO EN LA API:\n\n" . implode("\n\n", $failedRoutes));
    }
}
