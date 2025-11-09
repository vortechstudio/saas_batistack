<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
