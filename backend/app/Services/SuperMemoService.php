<?php

namespace App\Services;

class SuperMemoService
{
    /**
     * Calcula los nuevos parámetros de repetición espaciada usando el algoritmo SM-2.
     * 
     * @param int $quality Calidad de respuesta del 0 al 5 (0=Blanco total, 5=Perfecto)
     * @param int $repetitions Veces seguidas que se ha respondido correctamente
     * @param int $interval Intervalo actual en días
     * @param float $easeFactor Factor de facilidad actual (default 2.5)
     * @return array
     */
    public function calculate(int $quality, int $repetitions, int $interval, float $easeFactor): array
    {
        // Si la calidad es >= 3, se considera un acierto.
        if ($quality >= 3) {
            if ($repetitions === 0) {
                $interval = 1;
            } elseif ($repetitions === 1) {
                $interval = 6;
            } else {
                $interval = (int) round($interval * $easeFactor);
            }
            $repetitions++;
        } else {
            // Si el estudiante falló (0, 1, 2), se resetean las repeticiones
            $repetitions = 0;
            $interval = 1;
        }

        // Se recalcula el Ease Factor (Factor de Facilidad)
        $easeFactor = $easeFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        
        // El factor mínimo según SM-2 nunca debe ser menor a 1.3
        if ($easeFactor < 1.3) {
            $easeFactor = 1.3;
        }

        return [
            'repetitions' => $repetitions,
            'interval' => $interval,
            'ease_factor' => round($easeFactor, 3),
            'next_review_at' => now()->addDays($interval),
        ];
    }
}
