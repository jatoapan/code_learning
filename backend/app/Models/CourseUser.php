<?php

namespace App\Models;

use App\Enums\CourseRole;
use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseUser extends Pivot
{
    protected $table = 'course_user';

    protected $fillable = [
        'course_id',
        'user_id',
        'role',
        'status',
        'xp',
        'progress_percent',
    ];

    protected $casts = [
        'role' => CourseRole::class,
        'status' => EnrollmentStatus::class,
        'progress_percent' => 'decimal:2',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
