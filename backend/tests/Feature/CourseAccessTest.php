<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class CourseAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'professor']);
    }

    /**
     * Sprint 2: Un estudiante NO puede crear un curso → 403
     */
    public function test_student_cannot_create_a_course()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($student, 'api')->postJson('/api/v1/courses', [
            'title'       => 'Curso Trampa',
            'description' => 'Intento no autorizado',
            'status'      => 'public',
            'category'    => 'programming',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Sprint 2: Un estudiante NO puede acceder a un curso en estado 'draft' si no es el owner → 403
     */
    public function test_student_cannot_access_draft_course()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $professor = User::factory()->create();
        $professor->assignRole('professor');

        $course = Course::create([
            'id'          => Str::uuid(),
            'title'       => 'Curso Borrador',
            'slug'        => 'curso-borrador',
            'description' => 'No publicado',
            'category'    => 'programming',
            'status'      => 'draft',
            'owner_id'    => $professor->id,
        ]);

        $response = $this->actingAs($student, 'api')->getJson("/api/v1/courses/{$course->id}");

        // El estudiante no puede ver un curso en draft que no le pertenece
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Esperaba 403 o 404, obtuvo: {$response->status()}"
        );
    }

    /**
     * Sprint 2: Un estudiante NO puede inscribirse en un curso 'draft' → debe ser bloqueado
     */
    public function test_student_cannot_enroll_in_draft_course()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $professor = User::factory()->create();
        $professor->assignRole('professor');

        $course = Course::create([
            'id'          => Str::uuid(),
            'title'       => 'Borrador',
            'slug'        => 'borrador-enroll',
            'description' => 'x',
            'category'    => 'programming',
            'status'      => 'draft',
            'owner_id'    => $professor->id,
        ]);

        $response = $this->actingAs($student, 'api')->postJson("/api/v1/courses/{$course->id}/enrollments");

        $this->assertTrue(
            in_array($response->status(), [403, 404, 422]),
            "Esperaba 403/404/422, obtuvo: {$response->status()}"
        );
    }

    /**
     * Sprint 2: BOLA — Un estudiante NO puede crear un módulo en un curso ajeno → 403
     */
    public function test_student_cannot_create_module_in_foreign_course()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $professor = User::factory()->create();
        $professor->assignRole('professor');

        $course = Course::create([
            'id'          => Str::uuid(),
            'title'       => 'Curso del Profesor',
            'slug'        => 'curso-profesor',
            'description' => 'Propiedad del profesor',
            'category'    => 'programming',
            'status'      => 'public',
            'owner_id'    => $professor->id,
        ]);

        $response = $this->actingAs($student, 'api')->postJson("/api/v1/courses/{$course->id}/modules", [
            'title'       => 'Módulo Trampa',
            'description' => 'Inyectado ilegalmente',
            'order'       => 1,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Sprint 2: BOLA — Un profesor NO puede editar el módulo de un curso ajeno → 403
     */
    public function test_professor_cannot_edit_module_of_foreign_course()
    {
        $ownerProfessor = User::factory()->create();
        $ownerProfessor->assignRole('professor');

        $attackerProfessor = User::factory()->create();
        $attackerProfessor->assignRole('professor');

        $course = Course::create([
            'id'          => Str::uuid(),
            'title'       => 'Curso Original',
            'slug'        => 'curso-original',
            'description' => 'Del owner',
            'category'    => 'programming',
            'status'      => 'public',
            'owner_id'    => $ownerProfessor->id,
        ]);

        $module = Module::create([
            'course_id'   => $course->id,
            'title'       => 'Módulo Original',
            'description' => 'Legítimo',
            'order'       => 1,
        ]);

        // El profesor atacante intenta editar un módulo que no es suyo
        $response = $this->actingAs($attackerProfessor, 'api')->putJson("/api/v1/modules/{$module->id}", [
            'title' => 'Módulo Hackeado',
        ]);

        $response->assertStatus(403);
    }
}
