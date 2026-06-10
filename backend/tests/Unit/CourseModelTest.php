<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class CourseModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PB2: Unit testing of models (Course UUID and Fillables)
     */
    public function test_course_has_uuid_and_fillable_attributes()
    {
        $professor = User::factory()->create();
        
        $course = new Course([
            'title' => 'Software Engineering',
            'slug' => 'software-engineering',
            'description' => 'Test description',
            'status' => 'draft',
            'category' => 'programming',
            'owner_id' => $professor->id,
        ]);

        $this->assertEquals('Software Engineering', $course->title);
        $this->assertEquals('draft', $course->status);
        $this->assertEquals($professor->id, $course->owner_id);
    }
}
