<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MaterialUser extends Pivot
{
    protected $table = 'material_user';

    protected $fillable = [
        'material_id',
        'user_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
