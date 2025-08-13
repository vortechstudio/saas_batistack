<?php

use App\Jobs\CheckExpiringLicensesJob;
use App\Models\License;
use App\Models\User;
use App\Notifications\LicenseExpiringNotification;
use App\Enums\LicenseStatus;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

beforeEach(function () {
    Notification::fake();
    // Fixer l'heure pour les tests
    Carbon::setTestNow('2024-01-15 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

describe('CheckExpiringLicensesJob', function () {
    test('sends notifications for licenses expiring within 30 days', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);

        // Créer une licence qui expire exactement dans 15 jours
        $expiringLicense = License::factory()->create([
            'expires_at' => Carbon::parse('2024-01-30 12:00:00'), // 15 jours après le 15 janvier
            'status' => LicenseStatus::ACTIVE
        ]);

        $job = new CheckExpiringLicensesJob();
        $job->handle();

        Notification::assertSentTo(
            $adminUser,
            LicenseExpiringNotification::class
        );
    });

    test('does not send notifications for licenses expiring after 30 days', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);
        License::factory()->create([
            'expires_at' => Carbon::parse('2024-03-01 12:00:00'), // 45 jours après
            'status' => LicenseStatus::ACTIVE
        ]);

        $job = new CheckExpiringLicensesJob();
        $job->handle();

        Notification::assertNothingSent();
    });

    test('sends notifications for licenses expiring in exactly 30 days', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);

        $expiringLicense = License::factory()->create([
            'expires_at' => Carbon::parse('2024-02-14 12:00:00'), // 30 jours après le 15 janvier
            'status' => LicenseStatus::ACTIVE
        ]);

        $job = new CheckExpiringLicensesJob();
        $job->handle();

        Notification::assertSentTo(
            $adminUser,
            LicenseExpiringNotification::class
        );
    });

    test('sends notifications for licenses expiring in exactly 7 days', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);

        $expiringLicense = License::factory()->create([
            'expires_at' => Carbon::parse('2024-01-22 12:00:00'), // 7 jours après le 15 janvier
            'status' => LicenseStatus::ACTIVE
        ]);

        $job = new CheckExpiringLicensesJob();
        $job->handle();

        Notification::assertSentTo(
            $adminUser,
            LicenseExpiringNotification::class
        );
    });

    test('sends notifications for licenses expiring in exactly 3 days', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);

        $expiringLicense = License::factory()->create([
            'expires_at' => Carbon::parse('2024-01-18 12:00:00'), // 3 jours après le 15 janvier
            'status' => LicenseStatus::ACTIVE
        ]);

        $job = new CheckExpiringLicensesJob();
        $job->handle();

        Notification::assertSentTo(
            $adminUser,
            LicenseExpiringNotification::class
        );
    });

    test('sends notifications for licenses expiring in exactly 1 day', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);

        $expiringLicense = License::factory()->create([
            'expires_at' => Carbon::parse('2024-01-16 12:00:00'), // 1 jour après le 15 janvier
            'status' => LicenseStatus::ACTIVE
        ]);

        $job = new CheckExpiringLicensesJob();
        $job->handle();

        Notification::assertSentTo(
            $adminUser,
            LicenseExpiringNotification::class
        );
    });

    test('does not send notifications for licenses expiring in 14 days', function () {
        $adminUser = User::factory()->create(['email' => 'admin@batistack.com']);

        // 14 jours n'est pas dans la liste [30, 15, 7, 3, 1]
        License::factory()->create([
            'expires_at' => Carbon::parse('2024-01-29 12:00:00'), // 14 jours après le 15 janvier
            'status' => LicenseStatus::ACTIVE
        ]);

        $job = new CheckExpiringLicensesJob();
        $job->handle();

        Notification::assertNothingSent();
    });
});
