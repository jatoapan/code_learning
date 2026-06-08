<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Judge0Service
{
    protected $baseUrl;
    protected $token;
    protected $host;

    public function __construct()
    {
        $this->baseUrl = env('JUDGE0_URL', 'https://judge0-ce.p.rapidapi.com');
        $this->token = env('JUDGE0_TOKEN', '');
        $this->host = env('JUDGE0_HOST', 'judge0-ce.p.rapidapi.com');
    }

    /**
     * Envía el código a Judge0 para ser evaluado
     * 
     * @param int $languageId El ID del lenguaje (ej. 71 para Python 3)
     * @param string $sourceCode El código enviado por el estudiante
     * @param string|null $expectedOutput La salida esperada del caso de prueba
     * @param string|null $stdin La entrada estándar (opcional)
     * @return array
     */
    public function submitCode(int $languageId, string $sourceCode, ?string $expectedOutput = null, ?string $stdin = null)
    {
        $payload = [
            'language_id' => $languageId,
            'source_code' => $sourceCode,
        ];

        if ($expectedOutput) {
            $payload['expected_output'] = $expectedOutput;
        }

        if ($stdin) {
            $payload['stdin'] = $stdin;
        }

        try {
            $request = Http::withHeaders($this->getHeaders());

            // Si es RapidAPI u otra URL externa (Petición síncrona: wait=true)
            $response = $request->post($this->baseUrl . '/submissions?base64_encoded=false&wait=true', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error de Judge0: ' . $response->body());
            return ['error' => 'No se pudo conectar con el motor de compilación.'];
            
        } catch (\Exception $e) {
            Log::error('Excepción en Judge0Service: ' . $e->getMessage());
            return ['error' => 'Fallo interno al compilar.'];
        }
    }

    private function getHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Si usamos RapidAPI, el header es X-RapidAPI-Key
        if (str_contains($this->baseUrl, 'rapidapi.com')) {
            $headers['X-RapidAPI-Key'] = $this->token;
            $headers['X-RapidAPI-Host'] = $this->host;
        } else {
            // Si es auto-hospedado, usamos el estándar de Judge0 X-Auth-Token
            if (!empty($this->token)) {
                $headers['X-Auth-Token'] = $this->token;
            }
        }

        return $headers;
    }
}
