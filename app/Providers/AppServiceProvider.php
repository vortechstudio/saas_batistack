<?php

namespace App\Providers;

use App\Models\Customer\Customer;
use App\Models\Helpdesk\Blog;
use App\Models\Helpdesk\Incident;
use App\Models\Helpdesk\KbArticle;
use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketMessage;
use App\Observers\Customer\CustomerObserver;
use App\Observers\Helpdesk\IncidentObserver;
use App\Observers\Helpdesk\SlugObserver;
use App\Observers\Helpdesk\TicketMessageObserver;
use App\Observers\Helpdesk\TicketObserver;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Filament Processor
        FilamentColor::register([
            'primary' => "#0050d8",
            'secondary' => '#010b40'
        ]);

        // Observer
        Customer::observe(CustomerObserver::class);
        Ticket::observe(TicketObserver::class);
        TicketMessage::observe(TicketMessageObserver::class);
        Blog::observe(SlugObserver::class);
        KbArticle::observe(SlugObserver::class);
        Incident::observe(IncidentObserver::class);
    }
}
