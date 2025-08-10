<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EscalateNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $adminUsers = User::whereRaw("email LIKE '%@batistack.com'")->get();

        // Escalade des notifications critiques non lues depuis plus de 1 heure
        $criticalNotifications = Notification::whereNull('read_at')
            ->whereJsonContains('data->priority', 'critical')
            ->where('created_at', '<', now()->subHour())
            ->get();

        foreach ($criticalNotifications as $notification) {
            foreach ($adminUsers as $admin) {
                $admin->notify(new SystemAlertNotification(
                    'Escalade: Notification critique non lue',
                    "Une notification critique n'a pas été lue depuis plus d'1 heure: {$notification->data['title']}",
                    'critical',
                    "/admin/notification-center"
                ));
            }
        }

        // Escalade des notifications haute priorité non lues depuis plus de 4 heures
        $highPriorityNotifications = Notification::whereNull('read_at')
            ->whereJsonContains('data->priority', 'high')
            ->where('created_at', '<', now()->subHours(4))
            ->get();

        foreach ($highPriorityNotifications as $notification) {
            foreach ($adminUsers as $admin) {
                $admin->notify(new SystemAlertNotification(
                    'Escalade: Notification haute priorité non lue',
                    "Une notification haute priorité n'a pas été lue depuis plus de 4 heures: {$notification->data['title']}",
                    'warning',
                    "/admin/notification-center"
                ));
            }
        }

        // Rapport quotidien des notifications non lues
        $unreadCount = Notification::whereNull('read_at')->count();
        if ($unreadCount > 10) {
            foreach ($adminUsers as $admin) {
                $admin->notify(new SystemAlertNotification(
                    'Rapport: Nombreuses notifications non lues',
                    "Il y a actuellement {$unreadCount} notifications non lues dans le système",
                    'info',
                    "/admin/notification-center"
                ));
            }
        }
    }
}
