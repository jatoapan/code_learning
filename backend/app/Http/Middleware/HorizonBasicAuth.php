<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HorizonBasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Require Basic Auth matching the environment variables
        // HORIZON_USER and HORIZON_PASSWORD
        $expectedUser = env('HORIZON_USER', 'admin');
        $expectedPassword = env('HORIZON_PASSWORD', 'prolecom2026');

        if ($request->getUser() !== $expectedUser || $request->getPassword() !== $expectedPassword) {
            $headers = ['WWW-Authenticate' => 'Basic'];
            return response('Acceso Denegado a Horizon.', 401, $headers);
        }

        return $next($request);
    }
}
