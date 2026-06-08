<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'itemable_type',
        'itemable_id',
        'order',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}
