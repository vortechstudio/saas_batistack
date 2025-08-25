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
                <x-mary-button label="Messages" icon="o-envelope" link="###" class="btn-ghost btn-sm" responsive />
                <x-mary-button label="Notifications" icon="o-bell" link="###" class="btn-ghost btn-sm" responsive />
                <x-mary-dropdown :label="auth()->user()->fullname" icon="o-user" class="btn-outline btn-primary">
                    <x-mary-menu-item title="It should align correctly on right side" />
                    <x-mary-menu-item title="Yes!" />
                </x-mary-dropdown>
            </x-slot:actions>
        </x-mary-nav>
        <x-mary-main with-nav full-width>
 
            {{-- This is a sidebar that works also as a drawer on small screens --}}
            {{-- Notice the `main-drawer` reference here --}}
            <x-slot:sidebar drawer="main-drawer" collapsible collapse-text="Réduire" class="bg-blue-800 text-white">                
    
                {{-- Activates the menu item when a route matches the `link` property --}}
                <x-mary-menu activate-by-route active-bg-color="bg-blue-900 text-white font-black">
                    <x-mary-menu-item title="Accueil" icon="o-home" link="{{ route('dashboard') }}" />
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
