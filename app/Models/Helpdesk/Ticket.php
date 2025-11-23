<?php

namespace App\Models\Helpdesk;

use App\Enum\Helpdesk\TicketPriorityEnum;
use App\Enum\Helpdesk\TicketStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'last_reply_at' => 'datetime',
            'closed_at' => 'datetime',
            'status' => TicketStatusEnum::class,
            'priority' => TicketPriorityEnum::class,
        ];
    }
}
