<?php

namespace App\Models\Helpdesk;

use App\Enum\Helpdesk\ComponentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemComponents extends Model
{
    use HasFactory;
    protected $guarded = [];


    protected $casts = [
        'status' => ComponentStatusEnum::class,
    ];
}
