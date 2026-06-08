<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
        'prerequisite_module_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function prerequisiteModule()
    {
        return $this->belongsTo(Module::class, 'prerequisite_module_id');
    }

    public function items()
    {
        return $this->hasMany(ModuleItem::class);
    }

    public function forumThreads()
    {
        return $this->morphMany(ForumThread::class, 'forumable');
    }
}
