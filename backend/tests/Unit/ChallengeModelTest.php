<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Challenge;
use App\Models\Module;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChallengeModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PB8: Challenge Structure - Unit testing
     */
    public function test_challenge_attributes_and_difficulty_levels()
    {
        $professor = User::factory()->create();
        
        $course = Course::create([
            'title' => 'Test Course',
            'slug' => 'test-course',
            'description' => 'Desc',
            'status' => 'draft',
            'category' => 'programming',
            'owner_id' => $professor->id,
        ]);

        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Test Module',
            'order' => 1,
        ]);

        $challenge = Challenge::create([
            'module_id' => $module->id,
            'title' => 'Two Sum',
            'description' => 'Find two numbers that add up to target',
            'difficulty' => 'easy', // 3 difficulty levels validation
            'points' => 50,
            'creator_id' => $professor->id,
            'language_id' => 71, // dummy
            'language_name' => 'python',
            'status' => 'published',
        ]);

        $testCase = \App\Models\ChallengeTestCase::create([
            'challenge_id' => $challenge->id,
            'input' => '[2,7,11,15], 9',
            'expected_output' => '[0,1]',
            'is_hidden' => true,
        ]);

        $this->assertEquals('easy', $challenge->difficulty->value);
        $this->assertTrue($testCase->is_hidden); // Hidden test case validation
    }
}
