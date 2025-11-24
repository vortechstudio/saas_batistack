<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body class="font-sans antialiased">
        <x-mary-nav sticky full-width>
            <x-slot:brand>
                {{-- Drawer toggle for "main-drawer" --}}
                <label for="main-drawer" class="lg:hidden mr-3">
                    <x-icon name="heroicon-o-bars-3" class="cursor-pointer" />
                </label>

                {{-- Brand --}}
                <x-app-logo />
            </x-slot:brand>

            {{-- Right side actions --}}
            <x-slot:actions>
                <div class="drawer drawer-end">
                    <input id="my-drawer" type="checkbox" class="drawer-toggle" />
                    <div class="drawer-content indicator">
                        <!-- Page content here -->
                        <label for="my-drawer" class="btn-circle btn-sm drawer-button">@svg('heroicon-o-bell')</label>
                        <x-mary-badge :value="auth()->user()->unreadNotifications()->count()" class="badge badge-primary badge-xs indicator-item" />
                    </div>
                    <div class="drawer-side">
                        <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
                        <ul class="menu bg-base-200 text-base-content min-h-full w-[30%] p-4">
                        <!-- Sidebar content here -->
                            @foreach (auth()->user()->unreadNotifications as $notification)
                                <li>
                                    <div role="alert" class="alert alert-vertical sm:alert-horizontal alert-{{ $notification->data['iconColor'] }} mb-2">
                                        @php
                                        $icon = $notification->data['icon'];
                                        @endphp
                                        @svg($icon, 'w-6 h-6')
                                        <div>
                                            <h3 class="font-bold">{{ $notification->data['title'] }}</h3>
                                            <div class="text-xs">{{ $notification->data['body'] }}</div>
                                        </div>
                                        @isset($notification->data['actions'])
                                            @if(count($notification->data['actions']) > 0)
                                                <button class="btn btn-sm">See</button>
                                            @endif
                                        @endisset
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <x-mary-dropdown :label="auth()->user()->fullname" icon="o-user" class="btn-outline btn-primary">
                    <div class="flex flex-col w-[250px] text-lg m-5">
                        <span class="font-black text-blue-800 pb-1">{{ auth()->user()->fullname }}</span>
                        <span class="text-sm text-gray-400">{{ auth()->user()->email }}</span>
                        <span class="text-sm text-gray-400">Identifiant: <strong>{{ auth()->user()->customer->code_client }}</strong></span>
                    </div>
                    <x-separator />
                    <div class="m-5">
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </div>
                </x-mary-dropdown>
            </x-slot:actions>
        </x-mary-nav>
        <x-mary-main with-nav full-width>

            {{-- This is a sidebar that works also as a drawer on small screens --}}
            {{-- Notice the `main-drawer` reference here --}}
            <x-slot:sidebar drawer="main-drawer" collapsible collapse-text="Réduire" class="bg-blue-800 text-white">

                {{-- Activates the menu item when a route matches the `link` property --}}
                <x-mary-menu activate-by-route active-bg-color="bg-blue-900 text-white font-black">
                    <x-mary-menu-item title="Tableau de Bord" icon="o-home" link="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" />
                    <x-mary-menu-sub title="Commercial" icon="o-document" icon-classes="text-warning">
                        <x-mary-menu-item title="Clients" icon="o-user" link="{{ route('admin.commerce.customers') }}" />
                        <x-mary-menu-item title="Produits" icon="o-archive-box" link="#" />
                        <x-mary-menu-item title="Commandes" icon="o-document" link="#" />
                        <x-mary-menu-item title="Factures" icon="o-document-currency-euro" link="#" />
                    </x-mary-menu-sub>
                    <x-mary-menu-sub title="Gestion des Services" icon="o-document">
                        <x-mary-menu-item title="Services" icon="o-user" link="#" />
                        <x-mary-menu-item title="Domaines" icon="o-user" link="#" />
                    </x-mary-menu-sub>
                    <x-mary-menu-sub title="Assistances & Support" icon="o-document">
                        <x-mary-menu-item title="Blog" icon="o-user" link="#" />
                        <x-mary-menu-sub title="Base de connaissance" icon="o-document">
                            <x-mary-menu-item title="Catégories" icon="o-user" link="#" />
                            <x-mary-menu-item title="Articles" icon="o-user" link="#" />
                        </x-mary-menu-sub>
                        <x-mary-menu-item title="Status des Systèmes" icon="o-user" link="#" />
                        <x-mary-menu-item title="Tickets" icon="o-user" link="#" />
                    </x-mary-menu-sub>
                </x-mary-menu>
            </x-slot:sidebar>

            {{-- The `$slot` goes here --}}
            <x-slot:content>
                {{ $slot }}
                @livewire('notifications')
            </x-slot:content>
        </x-mary-main>
        @vite('resources/js/app.js')
        @livewireScripts
        @filamentScripts

    </body>
</html>
