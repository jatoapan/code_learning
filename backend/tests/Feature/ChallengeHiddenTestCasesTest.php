<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Challenge;
use App\Models\ChallengeTestCase;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ChallengeHiddenTestCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'student']);
    }

    /**
     * PB8: Hidden test case validation (Security check)
     */
    public function test_hidden_test_cases_are_not_exposed_to_students()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $professor = User::factory()->create();

        $course = Course::create([
            'id' => Str::uuid(),
            'title' => 'Python 101',
            'slug' => 'python-101',
            'description' => 'Desc',
            'category' => 'programming',
            'level' => 'beginner',
            'status' => 'public',
            'creator_id' => $professor->id
        ]);

        $module = Module::create([
            'id' => Str::uuid(),
            'course_id' => $course->id,
            'title' => 'Loops',
            'description' => 'Desc',
            'order' => 1
        ]);

        $challenge = Challenge::create([
            'id' => Str::uuid(),
            'module_id' => $module->id,
            'title' => 'Sum two numbers',
            'description' => 'Return the sum',
            'difficulty' => 'easy',
            'points' => 10
        ]);

        // Caso Público
        ChallengeTestCase::create([
            'id' => Str::uuid(),
            'challenge_id' => $challenge->id,
            'input' => '2 2',
            'expected_output' => '4',
            'is_hidden' => false,
            'weight' => 50
        ]);

        // Caso Oculto (Secreto)
        ChallengeTestCase::create([
            'id' => Str::uuid(),
            'challenge_id' => $challenge->id,
            'input' => '10 10',
            'expected_output' => '20',
            'is_hidden' => true,
            'weight' => 50
        ]);

        // Simulamos petición del estudiante
        $response = $this->actingAs($student, 'api')->getJson("/api/v1/challenges/{$challenge->id}");

        $response->assertStatus(200);
        
        // Verificamos que el servidor haya omitido el caso oculto
        $response->assertDontSee('10 10');
        $response->assertDontSee('20');
        
        // Verificamos que el caso público sí se muestre
        $response->assertSee('2 2');
    }
}
