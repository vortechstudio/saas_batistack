<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Statistiques des notifications -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7H4l5-5v5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ auth()->user()->notifications()->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Non lues</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ auth()->user()->unreadNotifications()->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Priorité haute</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ auth()->user()->notifications()->whereJsonContains('data->priority', 'high')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Aujourd'hui</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ auth()->user()->notifications()->whereDate('created_at', today())->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications récentes (aperçu) -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Notifications récentes</h3>
            <div class="space-y-3">
                @forelse(auth()->user()->notifications()->limit(5)->get() as $notification)
                    <div class="flex items-start p-3 {{ $notification->read_at ? 'bg-gray-50 dark:bg-gray-700' : 'bg-blue-50 dark:bg-blue-900' }} rounded-lg">
                        <div class="flex-shrink-0">
                            @php
                                $iconClass = match($notification->data['type'] ?? 'info') {
                                    'success' => 'text-green-500',
                                    'warning' => 'text-yellow-500',
                                    'danger' => 'text-red-500',
                                    default => 'text-blue-500'
                                };
                            @endphp
                            <div class="w-8 h-8 {{ $iconClass }} flex items-center justify-center">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if(!$notification->read_at)
                            <div class="flex-shrink-0">
                                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune notification</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vous n'avez aucune notification pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Table des notifications -->
        {{ $this->table }}
    </div>

    <script>
        // Auto-refresh toutes les 30 secondes
        setInterval(() => {
            @this.dispatch('$refresh');
        }, 30000);
    </script>
</x-filament-panels::page>
