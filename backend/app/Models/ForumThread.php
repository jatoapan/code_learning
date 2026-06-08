<?php

namespace App\Models;

use App\Enums\ThreadStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumThread extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'forumable_type',
        'forumable_id',
        'title',
        'body',
        'user_id',
        'status',
        'is_pinned',
        'vote_score',
        'view_count',
        'moderator_endorsed_at',
    ];

    protected $casts = [
        'status' => ThreadStatus::class,
        'is_pinned' => 'boolean',
        'moderator_endorsed_at' => 'datetime',
    ];

    public function forumable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(ForumPost::class, 'thread_id');
    }

    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function reports()
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
