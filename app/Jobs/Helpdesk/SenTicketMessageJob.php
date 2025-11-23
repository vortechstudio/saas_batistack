<?php

namespace App\Jobs\Helpdesk;

use App\Models\Helpdesk\TicketMessage;
use App\Notifications\Helpdesk\SendTicketNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SenTicketMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public TicketMessage $ticketMessage)
    {
    }

    public function handle(): void
    {
        $this->ticketMessage->user->notify(new SendTicketNotification($this->ticketMessage));
    }
}
