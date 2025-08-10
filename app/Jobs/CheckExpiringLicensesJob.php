<?php

namespace App\Jobs;

use App\Models\License;
use App\Models\User;
use App\Notifications\LicenseExpiringNotification;
use App\Notifications\LicenseExpiredNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiringLicensesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $adminUsers = User::whereRaw("email LIKE '%@batistack.com'")->get();

        // Licences expirant dans 30 jours
        $expiringIn30Days = License::where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->get();

        foreach ($expiringIn30Days as $license) {
            $daysUntilExpiry = now()->diffInDays($license->expires_at);

            // Notifier seulement à 30, 15, 7, 3, 1 jour(s)
            if (in_array($daysUntilExpiry, [30, 15, 7, 3, 1])) {
                foreach ($adminUsers as $admin) {
                    $admin->notify(new LicenseExpiringNotification($license, $daysUntilExpiry));
                }
            }
        }

        // Licences expirées
        $expiredLicenses = License::where('expires_at', '<', now())
            ->where('status', 'active')
            ->get();

        foreach ($expiredLicenses as $license) {
            // Marquer comme expirée
            $license->update(['status' => 'expired']);

            foreach ($adminUsers as $admin) {
                $admin->notify(new LicenseExpiredNotification($license));
            }
        }
    }
}
