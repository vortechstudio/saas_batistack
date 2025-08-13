<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use App\Notifications\CustomerInactiveNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckInactiveCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $adminUsers = User::whereRaw("email LIKE '%@batistack.com'")->get();

        // Clients inactifs depuis plus de 90 jours
        $inactiveCustomers = Customer::where('updated_at', '<', now()->subDays(90))
            ->where('status', 'active')
            ->get();

        foreach ($inactiveCustomers as $customer) {
            foreach ($adminUsers as $admin) {
                $admin->notify(new CustomerInactiveNotification($customer));
            }
        }
    }
}
