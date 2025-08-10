<?php

use App\Jobs\CheckExpiringLicensesJob;
use App\Jobs\CheckInactiveCustomersJob;
use App\Jobs\EscalateNotificationsJob;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CheckExpiringLicensesJob())
    ->dailyAt('09:00')
    ->name('check-expiring-licenses')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::job(new CheckInactiveCustomersJob())
    ->weeklyOn(1, '10:00')
    ->name('check-inactive-customers')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::job(new EscalateNotificationsJob())
    ->hourly()
    ->name('escalate-notifications')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::call(function () {
    Notification::where('created_at', '<', now()->subDays(90))
        ->delete();
})
    ->weeklyOn(0, '02:00')
    ->name('cleanup-old-notification');


Schedule::call(function () {
    $diskUsage = disk_free_space('/') / disk_total_space('/') * 100;

    if ($diskUsage < 10) {
        $admins = User::whereRaw("email LIKE '%@batistack.com'")->get();

        foreach ($admins as $admin) {
            $admin->notify(new SystemAlertNotification(
                title: 'Espace disque faible',
                message: "L'espace disque disponible est de seulement {$diskUsage} %",
                level: 'warning'
            ));
        }
    }
})
->hourly()
->name('system-health-check');

// Sauvegarde automatique quotidienne
Schedule::command('schedule:backup-sync --backup-only')
    ->dailyAt('02:00')
    ->name('daily-backup')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/backup.log'));

// Synchronisation automatique toutes les 4 heures
Schedule::command('schedule:backup-sync --sync-only')
    ->cron('0 */4 * * *')
    ->name('sync-external-systems')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/sync.log'));

// Sauvegarde complète hebdomadaire (dimanche à 01:00)
Schedule::command('schedule:backup-sync --backup-only')
    ->weeklyOn(0, '01:00')
    ->name('weekly-full-backup')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/backup.log'));

// Nettoyage des logs de synchronisation anciens (plus de 30 jours)
Schedule::call(function () {
    \App\Models\ExternalSyncLog::where('created_at', '<', now()->subDays(30))->delete();
})
    ->weeklyOn(0, '03:00')
    ->name('cleanup-old-sync-logs')
    ->withoutOverlapping()
    ->onOneServer();
