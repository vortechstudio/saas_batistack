<?php

use App\Jobs\CheckInactiveCustomersJob;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\CustomerInactiveNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
});

describe('CheckInactiveCustomersJob', function () {
    test('sends notifications for inactive customers', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);
        $inactiveCustomer = Customer::factory()->active()->create([
            'updated_at' => now()->subDays(91)
        ]);

        $job = new CheckInactiveCustomersJob();
        $job->handle();

        Notification::assertSentTo(
            $adminUser,
            CustomerInactiveNotification::class
        );
    });

    test('does not send notifications for active customers', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);
        Customer::factory()->active()->create([
            'updated_at' => now()->subDays(5)
        ]);

        $job = new CheckInactiveCustomersJob();
        $job->handle();

        Notification::assertNothingSent();
    });
});
