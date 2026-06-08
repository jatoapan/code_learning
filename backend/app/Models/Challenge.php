<?php

namespace App\Models;

use App\Enums\ChallengeDifficulty;
use App\Enums\ChallengeStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Challenge extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'difficulty',
        'language_id',
        'language_name',
        'starter_code',
        'points',
        'status',
        'review_feedback',
        'creator_id',
    ];

    protected $casts = [
        'difficulty' => ChallengeDifficulty::class,
        'status' => ChallengeStatus::class,
    ];

    public function moduleItems()
    {
        return $this->morphMany(ModuleItem::class, 'itemable');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function testCases()
    {
        return $this->hasMany(ChallengeTestCase::class);
    }

    public function attempts()
    {
        return $this->hasMany(ChallengeAttempt::class);
    }

    public function forumThreads()
    {
        return $this->morphMany(ForumThread::class, 'forumable');
    }
}
