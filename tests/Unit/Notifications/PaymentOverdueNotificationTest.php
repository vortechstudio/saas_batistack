<?php

use App\Notifications\PaymentOverdueNotification;
use App\Models\Customer;
use App\Models\User;
use App\Enums\NotificationType;
use Illuminate\Notifications\Messages\MailMessage;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->user = User::factory()->create();
    $this->notification = new PaymentOverdueNotification($this->customer, 150.00, 5);
});

describe('PaymentOverdueNotification', function () {
    test('creates mail message correctly', function () {
        $mailMessage = $this->notification->toMail($this->user);

        expect($mailMessage)->toBeInstanceOf(MailMessage::class)
            ->and($mailMessage->subject)->toContain('Paiement en retard')
            ->and($mailMessage->subject)->toContain($this->customer->company_name);
    });

    test('creates database notification correctly', function () {
        $databaseData = $this->notification->toDatabase($this->user);

        expect($databaseData['type'])->toBe(NotificationType::PAYMENT_OVERDUE)
            ->and($databaseData['customer_id'])->toBe($this->customer->id)
            ->and($databaseData['amount'])->toBe(150.00)
            ->and($databaseData['days_overdue'])->toBe(5)
            ->and($databaseData['priority'])->toBe('high');
    });
});
