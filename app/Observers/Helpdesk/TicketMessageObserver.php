<?php

namespace App\Observers\Helpdesk;

use App\Enum\Helpdesk\TicketStatusEnum;
use App\Jobs\Helpdesk\SenTicketMessageJob;
use App\Models\Helpdesk\TicketMessage;

class TicketMessageObserver
{
    public function created(TicketMessage $ticketMessage): void
    {
        $ticket = $ticketMessage->ticket;
        $ticket->last_reply_at = now();

        if ($ticketMessage->user_id === $ticket->user_id) {
            if (in_array($ticket->status, [TicketStatusEnum::PENDING, TicketStatusEnum::RESOLVED])) {
                $ticket->status = TicketStatusEnum::OPEN;
            }
        } else {
            // Si c'est un agent (admin) qui répond
            // On passe le ticket "En attente" de la réponse du client (sauf s'il est déjà résolu/fermé)
            if ($ticket->status === TicketStatusEnum::OPEN) {
                $ticket->status = TicketStatusEnum::PENDING;
            }
        }

        $ticket->save();

        dispatch(new SenTicketMessageJob($ticketMessage))->delay(now()->addSeconds(10));
    }
}
