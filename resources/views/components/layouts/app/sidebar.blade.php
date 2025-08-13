<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="antialiased min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('client.dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Espace Client')" class="grid">
                    <flux:navlist.item icon="home" :href="route('client.dashboard')" :current="request()->routeIs('client.dashboard')" wire:navigate>{{ __('Tableau de bord') }}</flux:navlist.item>
                    <flux:navlist.item icon="key" :href="route('client.licenses')" :current="request()->routeIs('client.licenses')" wire:navigate>{{ __('Mes Licences') }}</flux:navlist.item>
                    <flux:navlist.item icon="document-currency-euro" :href="route('client.invoices')" :current="request()->routeIs('client.invoices')" wire:navigate>{{ __('Mes Factures') }}</flux:navlist.item>
                    <flux:navlist.item icon="lifebuoy" :href="route('client.support')" :current="request()->routeIs('client.support')" wire:navigate>{{ __('Support') }}</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="book-open" href="https://docs.batistack.com" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item>

                <flux:navlist.item icon="chat-bubble-left-right" href="https://support.batistack.com" target="_blank">
                {{ __('Centre d\'aide') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    @if(auth()->user()->customer)
                                        <span class="truncate text-xs text-blue-600 dark:text-blue-400">{{ auth()->user()->customer->company_name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="user" wire:navigate>{{ __('Mon Profil') }}</flux:menu.item>
                        <flux:menu.item :href="route('settings.password')" icon="lock-closed" wire:navigate>{{ __('Mot de passe') }}</flux:menu.item>
                        <flux:menu.item :href="route('settings.appearance')" icon="paint-brush" wire:navigate>{{ __('Apparence') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Se déconnecter') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    @if(auth()->user()->customer)
                                        <span class="truncate text-xs text-blue-600 dark:text-blue-400">{{ auth()->user()->customer->company_name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="user" wire:navigate>{{ __('Mon Profil') }}</flux:menu.item>
                        <flux:menu.item :href="route('settings.password')" icon="lock-closed" wire:navigate>{{ __('Mot de passe') }}</flux:menu.item>
                        <flux:menu.item :href="route('settings.appearance')" icon="paint-brush" wire:navigate>{{ __('Apparence') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Se déconnecter') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @livewire('notifications')

        @filamentScripts
        @vite('resources/js/app.js')
        @fluxScripts
    </body>
</html>
