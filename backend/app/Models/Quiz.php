<?php

namespace App\Models;

use App\Enums\QuizMode;
use App\Enums\QuizStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'time_limit',
        'max_attempts',
        'passing_score',
        'random_question_limit',
        'status',
        'mode',
        'answers_visible_after',
    ];

    protected $casts = [
        'passing_score' => 'decimal:2',
        'status' => QuizStatus::class,
        'mode' => QuizMode::class,
        'answers_visible_after' => 'datetime',
    ];

    public function moduleItems()
    {
        return $this->morphMany(ModuleItem::class, 'itemable');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
