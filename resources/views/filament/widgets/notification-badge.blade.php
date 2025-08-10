<div class="relative">
    @if($unreadCount > 0)
        <div class="fixed top-4 right-4 z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 max-w-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        Notifications ({{ $unreadCount }})
                    </h3>
                    @if($highPriorityCount > 0)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            {{ $highPriorityCount }} urgente(s)
                        </span>
                    @endif
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @forelse($recentNotifications as $notification)
                        <div class="flex items-start space-x-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <div class="flex-shrink-0">
                                @php
                                    $iconClass = $notification->getTypeIcon();
                                    $colorClass = match($notification->getTypeColor()) {
                                        'danger' => 'text-red-500',
                                        'warning' => 'text-orange-500',
                                        'success' => 'text-green-500',
                                        'info' => 'text-blue-500',
                                        default => 'text-gray-500',
                                    };
                                @endphp
                                <x-dynamic-component :component="$iconClass" class="w-4 h-4 {{ $colorClass }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900 dark:text-white truncate">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $notification->data['message'] ?? '' }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-2">
                            Aucune notification récente
                        </p>
                    @endforelse
                </div>

                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                    <a href="{{ \App\Filament\Pages\NotificationCenter::getUrl() }}"
                       class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 font-medium">
                        Voir toutes les notifications →
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto-refresh toutes les 30 secondes
    setInterval(() => {
        window.location.reload();
    }, 30000);
</script>
