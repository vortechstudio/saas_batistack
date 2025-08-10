<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class NotificationController extends Controller
{
    /**
     * Obtenir le nombre de notifications
     */
    public function getNotificationCount(Request $request): JsonResponse
    {
        $lastCheck = $request->get('last_check');
        $lastCheckTime = $lastCheck ? Carbon::parse($lastCheck) : Carbon::now()->subHour();

        // Compter les licences qui expirent bientôt
        $expiringLicenses = License::where('expires_at', '<=', Carbon::now()->addDays(30))
            ->where('expires_at', '>', Carbon::now())
            ->count();

        // Compter les licences expirées
        $expiredLicenses = License::where('expires_at', '<', Carbon::now())
            ->count();

        // Compter les nouveaux clients depuis la dernière vérification
        $newCustomers = Customer::where('created_at', '>', $lastCheckTime)
            ->count();

        // Compter les activités récentes
        $recentActivities = Activity::where('created_at', '>', $lastCheckTime)
            ->count();

        // Calculer le total
        $totalCount = $expiringLicenses + $expiredLicenses + $newCustomers + $recentActivities;

        // Compter les nouvelles notifications depuis la dernière vérification
        $newCount = $newCustomers + $recentActivities;

        return response()->json([
            'total_count' => $totalCount,
            'new_count' => $newCount,
            'breakdown' => [
                'expiring_licenses' => $expiringLicenses,
                'expired_licenses' => $expiredLicenses,
                'new_customers' => $newCustomers,
                'recent_activities' => $recentActivities,
            ],
            'last_updated' => Carbon::now()->toISOString(),
        ]);
    }

    /**
     * Obtenir les notifications détaillées
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $notifications = [];

        // Licences qui expirent bientôt
        $expiringLicenses = License::with('customer', 'product')
            ->where('expires_at', '<=', Carbon::now()->addDays(30))
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('expires_at')
            ->limit(10)
            ->get();

        foreach ($expiringLicenses as $license) {
            $daysUntilExpiry = Carbon::now()->diffInDays($license->expires_at);
            $notifications[] = [
                'id' => 'license_expiring_' . $license->id,
                'type' => 'license_expiring',
                'title' => 'Licence expire bientôt',
                'message' => "La licence {$license->license_key} expire dans {$daysUntilExpiry} jour(s)",
                'priority' => $daysUntilExpiry <= 7 ? 'high' : 'medium',
                'created_at' => $license->created_at,
                'data' => [
                    'license_id' => $license->id,
                    'customer_name' => $license->customer->name,
                    'product_name' => $license->product->name,
                    'expires_at' => $license->expires_at,
                ],
                'actions' => [
                    [
                        'label' => 'Voir la licence',
                        'url' => route('filament.admin.resources.licenses.view', $license),
                    ],
                    [
                        'label' => 'Renouveler',
                        'url' => route('filament.admin.resources.licenses.edit', $license),
                    ],
                ],
            ];
        }

        // Licences expirées
        $expiredLicenses = License::with('customer', 'product')
            ->where('expires_at', '<', Carbon::now())
            ->orderBy('expires_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($expiredLicenses as $license) {
            $daysExpired = Carbon::now()->diffInDays($license->expires_at);
            $notifications[] = [
                'id' => 'license_expired_' . $license->id,
                'type' => 'license_expired',
                'title' => 'Licence expirée',
                'message' => "La licence {$license->license_key} a expiré il y a {$daysExpired} jour(s)",
                'priority' => 'high',
                'created_at' => $license->expires_at,
                'data' => [
                    'license_id' => $license->id,
                    'customer_name' => $license->customer->name,
                    'product_name' => $license->product->name,
                    'expires_at' => $license->expires_at,
                ],
                'actions' => [
                    [
                        'label' => 'Voir la licence',
                        'url' => route('filament.admin.resources.licenses.view', $license),
                    ],
                    [
                        'label' => 'Renouveler',
                        'url' => route('filament.admin.resources.licenses.edit', $license),
                    ],
                ],
            ];
        }

        // Nouveaux clients
        $newCustomers = Customer::where('created_at', '>', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($newCustomers as $customer) {
            $notifications[] = [
                'id' => 'new_customer_' . $customer->id,
                'type' => 'new_customer',
                'title' => 'Nouveau client',
                'message' => "Nouveau client enregistré : {$customer->name}",
                'priority' => 'medium',
                'created_at' => $customer->created_at,
                'data' => [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                ],
                'actions' => [
                    [
                        'label' => 'Voir le client',
                        'url' => route('filament.admin.resources.customers.view', $customer),
                    ],
                ],
            ];
        }

        // Activités récentes
        $recentActivities = Activity::with('user')
            ->where('created_at', '>', Carbon::now()->subDays(1))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentActivities as $activity) {
            $notifications[] = [
                'id' => 'activity_' . $activity->id,
                'type' => 'activity',
                'title' => 'Activité récente',
                'message' => $activity->description,
                'priority' => 'low',
                'created_at' => $activity->created_at,
                'data' => [
                    'activity_id' => $activity->id,
                    'user_name' => $activity->user->name ?? 'Système',
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                ],
                'actions' => [
                    [
                        'label' => 'Voir les détails',
                        'url' => route('filament.admin.resources.activity-logs.view', $activity),
                    ],
                ],
            ];
        }

        // Trier par priorité et date
        usort($notifications, function ($a, $b) {
            $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
            $aPriority = $priorityOrder[$a['priority']] ?? 0;
            $bPriority = $priorityOrder[$b['priority']] ?? 0;

            if ($aPriority === $bPriority) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }

            return $bPriority - $aPriority;
        });

        return response()->json([
            'notifications' => array_slice($notifications, 0, 50),
            'total_count' => count($notifications),
            'last_updated' => Carbon::now()->toISOString(),
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $notificationId = $request->get('notification_id');

        // Ici, vous pourriez implémenter la logique pour marquer comme lu
        // Par exemple, stocker dans une table user_notifications ou dans le cache

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        // Ici, vous pourriez implémenter la logique pour marquer toutes comme lues

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications marquées comme lues',
        ]);
    }
}
