<?php

namespace App\Models;

use App\Enums\MaterialType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'file_size',
        'creator_id',
        'moderator_endorsed_at',
    ];

    protected $casts = [
        'type' => MaterialType::class,
        'moderator_endorsed_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function moduleItems()
    {
        return $this->morphMany(ModuleItem::class, 'itemable');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(MaterialUser::class)
                    ->withPivot('viewed_at')
                    ->withTimestamps();
    }

    public function endorsements()
    {
        return $this->morphMany(Endorsement::class, 'endorseable');
    }
}
