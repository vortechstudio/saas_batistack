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
                        <ul class="menu bg-base-200 text-base-content min-h-full w-80 p-4">
                        <!-- Sidebar content here -->
                            @foreach (auth()->user()->unreadNotifications as $notification)
                                <li>
                                    <x-mary-alert :title="$notification->data['message']" />
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
                    <div class="flex flex-col w-[250px] m-5">
                        <div class="flex flex-row justify-between items-center mb-1">
                            <span>Compte</span>
                            <x-mary-badge value="{{ auth()->user()->customer->type_compte->label() }}" class="badge-info text-white" />
                        </div>
                        <div class="flex flex-row justify-between items-center mb-1">
                            <span>Moyen de paiement</span>
                            <x-mary-badge value="{{ auth()->user()->customer->hasPaymentMethods() ? 'Oui' : 'Non' }}" :class="auth()->user()->customer->hasPaymentMethods() ? 'text-white badge-success' : 'text-white badge-error'" />
                        </div>
                        <div class="flex flex-row justify-between items-center">
                            <span>Support</span>
                            <x-mary-badge value="{{ auth()->user()->customer->support_type->label() }}" class="badge text-white badge-{{ auth()->user()->customer->support_type->color() }}" />
                        </div>
                    </div>
                    <x-separator />
                    <div class="m-5">
                        <x-mary-menu-item title="Mon Compte" link="{{ route('client.account.dashboard') }}" />
                        <x-mary-menu-item title="Mes factures" link="{{ route('client.account.invoice') }}" />
                        <x-mary-menu-item title="Mes moyens de paiements" link="{{ route('client.account.method-payment') }}" />
                        <x-mary-menu-item title="Mes commandes" link="{{ route('client.account.orders') }}" />
                        <x-mary-menu-item title="Mes Services & Contrats" link="###" />
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
                    <x-mary-menu-item title="Accueil" icon="o-home" link="{{ route('client.dashboard') }}" />
                    <x-mary-menu-sub title="Logiciels & Services" icon="o-adjustments-horizontal">
                        <x-mary-menu-item title="Mes logiciels & Services" link="####" />
                        <x-mary-menu-item title="Sauvegarde en ligne" link="####" />
                        <x-mary-menu-item title="Catalogue" link="####" />
                    </x-mary-menu-sub>
                    <x-mary-menu-sub title="Formations" icon="o-book-open">
                        <x-mary-menu-item title="Mes formations" link="####" />
                        <x-mary-menu-item title="Catalogue" link="####" />
                    </x-mary-menu-sub>
                    <x-mary-menu-sub title="Assistance" icon="o-lifebuoy">
                        <x-mary-menu-item title="Contact" link="####" />
                        <x-mary-menu-item title="Centre d'aide" link="####" />
                        <x-mary-menu-item title="Mes Tickets" link="####" />
                    </x-mary-menu-sub>
                </x-mary-menu>
                <x-mary-button label="Souscrire" icon="o-shopping-bag" class="btn-outline btn-wide mt-10 mx-2" link="{{ route('client.account.cart.index') }}" />
            </x-slot:sidebar>

            {{-- The `$slot` goes here --}}
            <x-slot:content>
                {{ $slot }}
                @livewire('notifications')
            </x-slot:content>
        </x-mary-main>

        @filamentScripts
        @vite('resources/js/app.js')
    </body>
</html>
