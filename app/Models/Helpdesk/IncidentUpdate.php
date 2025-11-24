<?php

namespace App\Models\Helpdesk;

use App\Enum\Helpdesk\IncidentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentUpdate extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    protected $casts = [
        'status' => IncidentStatusEnum::class,
    ];
}
