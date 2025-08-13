<?php

use App\Filament\Widgets\ExpiringLicensesWidget;
use App\Models\License;
use App\Enums\LicenseStatus;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('ExpiringLicensesWidget', function () {
    test('displays licenses expiring within 30 days', function () {
        $expiringLicense = License::factory()->create([
            'expires_at' => now()->addDays(15),
            'status' => LicenseStatus::ACTIVE
        ]);

        $notExpiringLicense = License::factory()->create([
            'expires_at' => now()->addDays(45),
            'status' => LicenseStatus::ACTIVE
        ]);

        Livewire::test(ExpiringLicensesWidget::class)
            ->assertCanSeeTableRecords([$expiringLicense])
            ->assertCanNotSeeTableRecords([$notExpiringLicense]);
    });
});
