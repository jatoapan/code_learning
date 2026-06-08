<?php

namespace App\Models;

use App\Enums\InstitutionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo_path',
        'website',
        'type',
    ];

    protected $casts = [
        'type' => InstitutionType::class,
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
