<?php

namespace App\Console\Commands\Core;

use App\Services\Stripe\StripeService;
use Illuminate\Console\Command;

class PurgeStripeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge:stripe-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stripe = new StripeService();

        foreach($stripe->client->customers->all(['limit' => 200]) as $customer) {
            $stripe->client->customers->delete($customer->id);
            $this->info('Customer deleted: ' . $customer->id);
        }
    }
}
