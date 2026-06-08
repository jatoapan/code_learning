<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Judge0Service
{
    protected string $baseUrl;

    public function __construct()
    {
        // En producción (Railway) tomará la URL del contenedor de Judge0
        $this->baseUrl = config('services.judge0.url', 'http://localhost:2358');
    }

    /**
     * Envía un intento de código a Judge0 de forma asíncrona.
     */
    public function submit(string $sourceCode, int $languageId, ?string $stdin, ?string $expectedOutput, string $callbackUrl = null)
    {
        $payload = [
            'source_code' => $sourceCode,
            'language_id' => $languageId,
            'stdin' => $stdin,
            'expected_output' => $expectedOutput,
        ];

        if ($callbackUrl) {
            $payload['callback_url'] = $callbackUrl;
        }

        try {
            $response = Http::post("{$this->baseUrl}/submissions?base64_encoded=false&wait=false", $payload);

            if ($response->successful()) {
                return $response->json('token');
            }
            
            Log::error('Judge0 Submission Error', ['response' => $response->body()]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Judge0 Connection Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Consulta manualmente el estado de un token (útil como fallback si fallan los webhooks).
     */
    public function getSubmission(string $token)
    {
        $response = Http::get("{$this->baseUrl}/submissions/{$token}?base64_encoded=false");
        
        if ($response->successful()) {
            return $response->json();
        }
        
        return null;
    }
}
