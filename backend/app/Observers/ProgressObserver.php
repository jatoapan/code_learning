<?php

namespace App\Observers;

use App\Models\CourseUser;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\Challenge;
use App\Models\MaterialUser;
use App\Models\QuizAttempt;
use App\Models\ChallengeAttempt;

class ProgressObserver
{
    /**
     * Listen to the MaterialUser/QuizAttempt/ChallengeAttempt saved event.
     */
    public function saved($model)
    {
        $this->recalculateForUser($model);
    }

    /**
     * Listen to the deleted event (Soft Deletes).
     */
    public function deleted($model)
    {
        if ($model instanceof Material || $model instanceof Quiz || $model instanceof Challenge) {
            // Un profesor eliminó un recurso, hay que recalcular el % para todos los alumnos
            $this->recalculateForCourse($model);
        } else {
            // Un estudiante eliminó/reseteó su intento
            $this->recalculateForUser($model);
        }
    }

    protected function recalculateForUser($model)
    {
        // Determinar course_id y user_id
        $userId = $model->user_id ?? null;
        $courseId = $this->getCourseIdFromModel($model);

        if ($userId && $courseId) {
            $courseUser = CourseUser::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            if ($courseUser) {
                // Dummy logic for MVP: Recalcular iterando los module_items
                // $total = ... $completed = ...
                $newProgress = min(100.00, $courseUser->progress_percent + 1.00); 
                $courseUser->update(['progress_percent' => $newProgress]);
            }
        }
    }

    protected function recalculateForCourse($model)
    {
        $courseId = $this->getCourseIdFromModel($model);

        if ($courseId) {
            // Recalcular el progreso para todos los estudiantes activos en el curso
            // $students = CourseUser::where('course_id', $courseId)->get();
            // foreach ($students as $student) { ... }
        }
    }

    private function getCourseIdFromModel($model)
    {
        // Lógica de navegación del Patrón Composite para hallar el curso
        if ($model instanceof MaterialUser) return $model->material->moduleItems()->first()?->module->course_id;
        if ($model instanceof QuizAttempt) return $model->quiz->moduleItems()->first()?->module->course_id;
        if ($model instanceof ChallengeAttempt) return $model->challenge->module->course_id;
        
        if ($model instanceof Material) return $model->moduleItems()->first()?->module->course_id;
        if ($model instanceof Quiz) return $model->moduleItems()->first()?->module->course_id;
        if ($model instanceof Challenge) return $model->module->course_id;

        return null;
    }
}
