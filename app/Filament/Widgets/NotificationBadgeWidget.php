<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class NotificationBadgeWidget extends Widget
{
    protected string $view = 'filament.widgets.notification-badge';
    protected static ?int $sort = -10;
    protected int | string | array $columnSpan = 1;

    public function getViewData(): array
    {
        $user = Auth::user();

        return [
            'unreadCount' => $user?->unread_notifications_count ?? 0,
            'highPriorityCount' => $user?->high_priority_notifications_count ?? 0,
            'recentNotifications' => $user ? $user->notifications : collect()
                ->whereNull('read_at')
                ->latest()
                ->limit(5)
                ->get() ?? collect(),
        ];
    }
}
