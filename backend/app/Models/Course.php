<?php

namespace App\Models;

use App\Enums\CourseCategory;
use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category',
        'title',
        'slug',
        'description',
        'image_path',
        'status',
        'has_leaderboard',
        'owner_id',
    ];

    protected $casts = [
        'category' => CourseCategory::class,
        'status' => CourseStatus::class,
        'has_leaderboard' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(CourseUser::class)
                    ->withPivot(['role', 'status', 'xp', 'progress_percent'])
                    ->withTimestamps();
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function forumThreads()
    {
        return $this->morphMany(ForumThread::class, 'forumable');
    }
}
