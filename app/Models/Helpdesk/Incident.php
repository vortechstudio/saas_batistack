<?php

namespace App\Models\Helpdesk;

use App\Enum\Helpdesk\IncidentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Str;

class Incident extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'occurred_at' => 'timestamp',
            'resolved_at' => 'timestamp',
            'status' => IncidentStatusEnum::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(SystemComponents::class, 'incident_component');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(IncidentUpdate::class)->orderByDesc('created_at');
    }
}
