<?php

namespace App\Observers\Helpdesk;

use App\Models\Helpdesk\Ticket;

class TicketObserver
{
    public function creating(Ticket $ticket): void
    {
        $ticket->uuid = \Str::uuid();
        $ticket->save();
    }
}
