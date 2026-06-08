<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class EndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valida dinámicamente que ninguno de los 120 endpoints de la API arroje error >= 500.
     *
     * @return void
     */
    public function test_all_api_endpoints_do_not_crash()
    {
        // Preparar entorno base
        $this->artisan('db:seed');
        $admin = User::where('email', 'admin@prolecom.com')->first();
        if (!$admin) {
            $admin = User::factory()->create(['status' => 'active', 'xp' => 100]);
        }

        // Falsificar llamadas HTTP externas (ej. Judge0) para que el test no se cuelgue esperando respuesta
        \Illuminate\Support\Facades\Http::fake();

        $routes = Route::getRoutes();
        $failedRoutes = [];

        foreach ($routes as $route) {
            $uri = $route->uri();

            // Filtrar solo las rutas bajo /api/v1
            if (!str_starts_with($uri, 'api/v1')) {
                continue;
            }

            $method = $route->methods()[0];

            // Sustitución de parámetros dinámicos ({id}, {user_id}, etc.) por valor dummy 1
            $uriWithDummy = preg_replace('/\{[a-zA-Z0-9_]+\}/', '1', $uri);

            // Ejecución pura mediante $this->json()
            $response = $this->actingAs($admin)->json($method, $uriWithDummy, []);

            // Reporte claro si el status es >= 500 (Fatal Error/Crash)
            if ($response->status() >= 500) {
                $content = substr($response->getContent(), 0, 300); // Muestra los primeros 300 caracteres del error
                $failedRoutes[] = "❌ FAIL: [$method] /$uriWithDummy => HTTP " . $response->status() . "\n   Detalle: " . $content;
            }
        }

        // Si fallan rutas, PHPUnit detendrá la prueba y arrojará el listado explícito
        $this->assertEmpty($failedRoutes, "CRASH DETECTADO EN LA API:\n\n" . implode("\n\n", $failedRoutes));
    }
}
