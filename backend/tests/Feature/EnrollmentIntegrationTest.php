<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class EnrollmentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'professor']);
    }

    /**
     * PB6: Student-course integration testing (Valid Enrollment)
     */
    public function test_student_can_enroll_in_a_published_course()
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        
        $professor = User::factory()->create();
        
        $course = Course::create([
            'id' => Str::uuid(),
            'title' => 'Advanced Algorithms',
            'description' => 'Course description',
            'category' => 'programming',
            'level' => 'advanced',
            'status' => 'public',
            'creator_id' => $professor->id
        ]);

        $response = $this->actingAs($student, 'api')->postJson("/api/v1/courses/{$course->id}/enrollments");

        $response->assertStatus(201);
        
        // Verifica integridad de base de datos
        $this->assertDatabaseHas('course_user', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'role' => 'student'
        ]);
    }

    /**
     * PB6: Student-course integration testing (Duplicate Enrollment Prevention)
     */
    public function test_student_cannot_enroll_twice_in_same_course()
    {
        $student = User::factory()->create();
        $student->assignRole('student');
        
        $professor = User::factory()->create();
        
        $course = Course::create([
            'id' => Str::uuid(),
            'title' => 'Data Structures',
            'description' => 'Course description',
            'category' => 'programming',
            'level' => 'intermediate',
            'status' => 'public',
            'creator_id' => $professor->id
        ]);

        // Primera matrícula
        $this->actingAs($student, 'api')->postJson("/api/v1/courses/{$course->id}/enrollments");
        
        // Segunda matrícula (Intento duplicado)
        $response = $this->actingAs($student, 'api')->postJson("/api/v1/courses/{$course->id}/enrollments");

        // El sistema debe rechazar la petición (Generalmente 409 Conflict o 400)
        $this->assertTrue(in_array($response->status(), [400, 403, 409, 422]), 'Debería devolver código de error de cliente');
    }
}
