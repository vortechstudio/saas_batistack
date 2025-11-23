<?php

namespace App\Providers;

use App\Models\Helpdesk\Blog;
use App\Models\Helpdesk\KbArticle;
use App\Models\Helpdesk\TicketMessage;
use App\Observers\Helpdesk\SlugObserver;
use App\Observers\Helpdesk\TicketMessageObserver;
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
        TicketMessage::observe(TicketMessageObserver::class);
        Blog::observe(SlugObserver::class);
        KbArticle::observe(SlugObserver::class);
    }
}
