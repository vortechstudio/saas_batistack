<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="font-sans antialiased">
    <x-mary-nav sticky full-width>
        <x-slot:brand>
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-app-logo-icon />
            </label>
            <div>{{ config('app.name') }}</div>
        </x-slot:brand>
        <x-slot:actions>
            <div class="drawer drawer-end">
                <input id="my-drawer-4" type="checkbox" class="drawer-toggle" />
                <div class="drawer-content">
                    <!-- Page content here -->
                    <label for="my-drawer-4" class="drawer-button btn btn-circle btn-ghost btn-sm">
                        <x-icon name="heroicon-o-bell" class="w-4 h-4" />
                    </label>
                </div>
                <div class="drawer-side">
                    <label for="my-drawer-4" aria-label="close sidebar" class="drawer-overlay"></label>
                    <ul class="menu bg-base-200 text-base-content min-h-full w-80 p-4">
                    <!-- Sidebar content here -->
                    <li><a>Sidebar Item 1</a></li>
                    <li><a>Sidebar Item 2</a></li>
                    </ul>
                </div>
            </div>
            <x-mary-dropdown label="Mon compte" icon="o-user" class="w-[150px]" right>
                <div class="flex flex-col w-[350px] m-5">
                    <div class="font-bold text-lg text-blue-500">{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</div>
                    <div class="text-sm text-gray-500">{{ auth()->user()->email }}</div>
                    <div class="text-sm text-gray-500">Identifiant: <strong>{{ auth()->user()->customer->code_client }}</strong></div>
                </div>
                <x-mary-menu-separator />
                <div class="flex flex-col">
                    <div class="flex flex-row justify-between items-center mb-1">
                        <div class="text-gray-500">Connexion</div>
                        <div class="text-gray-500">{{ auth()->user()->customer->code_client }}</div>
                    </div>
                    <div class="flex flex-row justify-between items-center mb-1">
                        <div class="text-gray-500">Moyens de paiement</div>
                        <div class="text-gray-500">
                            @if(auth()->user()->customer->hasPaymentMethods())
                                <div class="badge badge-xl badge-success text-white">Validé</div>
                            @else
                                <div class="badge badge-xl badge-error text-white">Non validé</div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-mary-dropdown>
        </x-slot:actions>
    </x-mary-nav>
    <x-mary-main with-nav full-width>
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-blue-900 text-white">
            <div class="text-white text-2xl font-bold m-5">Hub de données</div>

            <x-mary-menu activate-by-route>
                <x-mary-menu-item title="Acceuil" icon="o-home" link="{{ route('dashboard') }}" />
                <x-mary-menu-item title="Messages" icon="o-envelope" link="###" />
                <x-mary-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-mary-menu-item title="Wifi" icon="o-wifi" link="####" />
                    <x-mary-menu-item title="Archives" icon="o-archive-box" link="####" />
                </x-mary-menu-sub>
                <x-mary-button class="btn-outline mt-5" label="Ajouter une license" icon="o-plus" />
            </x-mary-menu>
        </x-slot:sidebar>
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>
    <x-mary-toast />
</body>
</html>
