<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationType;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $adminUsers = User::where('email', 'like', '%@batistack.com')->get();

        if ($adminUsers->isEmpty()) {
            // Créer un utilisateur admin de test si aucun n'existe
            $adminUser = User::create([
                'name' => 'Admin Test',
                'email' => 'admin@batistack.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $adminUsers = collect([$adminUser]);
        }

        foreach ($adminUsers as $user) {
            // Notification de licence expirée (critique)
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::LICENSE_EXPIRED,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Licence expirée',
                    'message' => 'La licence du client ABC Corp a expiré il y a 3 jours.',
                    'priority' => 'critical',
                    'customer_name' => 'ABC Corp',
                    'license_key' => 'LIC-ABC-2024-001',
                    'expired_at' => now()->subDays(3)->toDateString(),
                    'action_url' => '/admin/licenses',
                ],
                'priority' => 1,
                'channels' => ['database', 'mail'],
                'created_at' => now()->subHours(2),
            ]);

            // Notification de licence qui expire bientôt
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::LICENSE_EXPIRING,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Licence expire bientôt',
                    'message' => 'La licence de XYZ Ltd expire dans 5 jours.',
                    'priority' => 'high',
                    'customer_name' => 'XYZ Ltd',
                    'license_key' => 'LIC-XYZ-2024-002',
                    'expires_at' => now()->addDays(5)->toDateString(),
                    'action_url' => '/admin/licenses',
                ],
                'priority' => 2,
                'channels' => ['database', 'mail'],
                'created_at' => now()->subHours(6),
            ]);

            // Notification de nouveau client
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::NEW_CUSTOMER,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Nouveau client enregistré',
                    'message' => 'Un nouveau client "Tech Solutions" s\'est inscrit.',
                    'priority' => 'medium',
                    'customer_name' => 'Tech Solutions',
                    'customer_email' => 'contact@techsolutions.com',
                    'action_url' => '/admin/customers',
                ],
                'priority' => 3,
                'channels' => ['database'],
                'created_at' => now()->subHours(12),
            ]);

            // Notification de paiement en retard
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::PAYMENT_OVERDUE,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Paiement en retard',
                    'message' => 'Le paiement de Global Inc est en retard de 15 jours.',
                    'priority' => 'high',
                    'customer_name' => 'Global Inc',
                    'amount' => '€2,500.00',
                    'days_overdue' => 15,
                    'action_url' => '/admin/customers',
                ],
                'priority' => 2,
                'channels' => ['database', 'mail'],
                'read_at' => now()->subHours(1), // Cette notification est lue
                'created_at' => now()->subDay(),
            ]);

            // Notification de client inactif
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::CUSTOMER_INACTIVE,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Client inactif',
                    'message' => 'Le client StartupCo n\'a pas utilisé sa licence depuis 30 jours.',
                    'priority' => 'low',
                    'customer_name' => 'StartupCo',
                    'days_inactive' => 30,
                    'last_activity' => now()->subDays(30)->toDateString(),
                    'action_url' => '/admin/customers',
                ],
                'priority' => 4,
                'channels' => ['database'],
                'created_at' => now()->subDays(2),
            ]);

            // Alerte système
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => NotificationType::SYSTEM_ALERT,
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Alerte système',
                    'message' => 'Espace disque faible sur le serveur principal (85% utilisé).',
                    'priority' => 'critical',
                    'level' => 'critical',
                    'disk_usage' => '85%',
                    'server' => 'srv-prod-01',
                ],
                'priority' => 1,
                'channels' => ['database', 'mail'],
                'created_at' => now()->subMinutes(30),
            ]);
        }
    }
}
