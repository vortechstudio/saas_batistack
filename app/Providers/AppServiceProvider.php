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
     * Effectue la configuration de démarrage : enregistre le thème Filament et attache les observers aux modèles.
     *
     * Enregistre le schéma de couleurs Filament (primary = "#0050d8", secondary = "#010b40") et attache les observers suivants :
     * - App\Models\Customer\Customer  => App\Observers\Customer\CustomerObserver
     * - App\Models\Ticket\Ticket      => App\Observers\Ticket\TicketObserver
     * - App\Models\TicketMessage      => App\Observers\Ticket\TicketMessageObserver
     * - App\Models\Blog\Blog          => App\Observers\SlugObserver
     * - App\Models\KbArticle\KbArticle=> App\Observers\SlugObserver
     * - App\Models\Incident\Incident  => App\Observers\Incident\IncidentObserver
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