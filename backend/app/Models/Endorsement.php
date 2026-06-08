<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endorsement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'endorseable_id',
        'endorseable_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function endorseable()
    {
        return $this->morphTo();
    }
}
