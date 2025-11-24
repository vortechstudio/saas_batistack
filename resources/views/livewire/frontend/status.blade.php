<div class="bg-white min-h-screen pb-24 font-sans text-slate-600" wire:poll.60s>

    {{-- TOP NAVIGATION --}}
    <div class="border-b border-slate-200 bg-white sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex-shrink-0">
                    <x-app-logo class="h-8 w-auto text-ovh-primary" />
                </a>
                <span class="text-xl font-semibold text-slate-900 tracking-tight border-l border-slate-200 pl-4 ml-1">Statut des services</span>
            </div>
            <div class="hidden md:block">
                <button wire:click="$set('showSubscribeModal', true)" class="bg-ovh-primary text-white px-4 py-2 rounded-md text-sm font-bold hover:bg-ovh-dark transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    S'abonner aux mises à jour
                </button>
            </div>
        </div>
    </div>

    {{-- NOTIFICATION FLASH --}}
    @if (session('status_success'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">{{ session('status_success') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-12">

        {{-- GLOBAL STATUS INDICATOR --}}
        <div class="{{ $this->globalStatus['color'] }} rounded-lg p-6 text-white shadow-sm mb-12 flex flex-col md:flex-row items-center justify-between text-center md:text-left transition-colors duration-500">
            <div class="flex items-center gap-4 mb-4 md:mb-0">
                @if($this->globalStatus['icon'] === 'check-circle')
                    <svg class="w-10 h-10 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                @elseif($this->globalStatus['icon'] === 'exclamation-triangle')
                    <svg class="w-10 h-10 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                @else
                    <svg class="w-10 h-10 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                @endif
                <div>
                    <span class="text-2xl font-bold block">{{ $this->globalStatus['label'] }}</span>
                    <span class="text-white/90 text-sm">{{ $this->globalStatus['message'] }}</span>
                </div>
            </div>
            <span class="text-white/80 text-xs bg-white/20 px-3 py-1 rounded-full">Actualisé à {{ now()->format('H:i') }}</span>
        </div>

        {{-- ACTIVE INCIDENTS --}}
        @if($activeIncidents->count() > 0)
            <div class="mb-16">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Incidents en cours</h2>
                <div class="space-y-8">
                    @foreach($activeIncidents as $incident)
                        <div class="bg-white rounded-lg border border-orange-200 shadow-sm overflow-hidden">
                            <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-orange-800">{{ $incident->title }}</h3>
                                <span class="text-xs font-bold uppercase tracking-wider text-orange-600 bg-orange-100 px-2 py-1 rounded">{{ $incident->status->getLabel() }}</span>
                            </div>
                            <div class="p-6">
                                <div class="mb-6 text-sm text-slate-500 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <strong>Services impactés :</strong>
                                    @foreach($incident->components as $comp)
                                        {{ $comp->name }}{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                </div>
                                <div class="space-y-8 relative pl-4 border-l-2 border-slate-100 ml-2">
                                    @foreach($incident->updates as $update)
                                        <div class="relative group">
                                            <div class="absolute -left-[21px] top-1 w-4 h-4 rounded-full bg-slate-200 border-2 border-white group-first:bg-orange-500"></div>
                                            <div class="text-sm text-slate-500 mb-1 font-medium">
                                                {{ $update->status->getLabel() }}
                                                <span class="font-normal text-slate-400">- {{ $update->created_at->translatedFormat('d F à H:i') }}</span>
                                            </div>
                                            <div class="text-slate-800 prose prose-sm">{!! nl2br(e($update->message)) !!}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- SYSTEM COMPONENTS --}}
        <div class="mb-16">
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-2xl font-bold text-slate-900">État des services</h2>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span> Opérationnel</span>
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span> Maintenance</span>
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span> Panne</span>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                @foreach($groups as $groupName => $components)
                    <div class="border-b border-slate-200 last:border-0" x-data="{ expanded: true }">
                        <div class="bg-slate-50 px-6 py-3 flex justify-between items-center cursor-pointer hover:bg-slate-100 transition select-none" @click="expanded = !expanded">
                            <h3 class="font-bold text-slate-700 text-sm uppercase tracking-wide">{{ $groupName ?: 'Général' }}</h3>
                            <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-200" :class="{'rotate-180': !expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <div class="divide-y divide-slate-100" x-show="expanded" x-collapse>
                            @foreach($components as $component)
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition group">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-slate-700">{{ $component->name }}</span>
                                        @if($component->description)
                                            <div class="relative ml-2" x-data="{ tooltip: false }">
                                                <svg @mouseenter="tooltip = true" @mouseleave="tooltip = false" class="w-4 h-4 text-slate-300 cursor-help hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div x-show="tooltip" class="absolute left-6 top-0 bg-slate-800 text-white text-xs rounded p-2 w-48 z-10" style="display: none;">
                                                    {{ $component->description }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if($component->status->value === 'operational')
                                            <span class="text-sm text-green-600 font-medium hidden sm:block">Opérationnel</span>
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        @elseif($component->status->value === 'performance_issues')
                                            <span class="text-sm text-blue-600 font-medium hidden sm:block">Dégradé</span>
                                            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                        @elseif($component->status->value === 'maintenance')
                                            <span class="text-sm text-blue-600 font-medium hidden sm:block">Maintenance</span>
                                            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
                                        @else
                                            <span class="text-sm text-red-600 font-medium hidden sm:block">Panne</span>
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PAST INCIDENTS --}}
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Historique des incidents</h2>

            <div class="space-y-8 border-l border-slate-200 ml-4">
                @forelse($pastIncidents as $incident)
                    <div class="relative pl-8 pb-8 group">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-2 border-slate-300 group-hover:border-ovh-primary transition-colors"></div>
                        <div class="mb-1 text-sm text-slate-500">{{ $incident->occurred_at->translatedFormat('d F Y') }}</div>
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-ovh-primary transition-colors">{{ $incident->title }}</h3>
                        <div class="text-sm text-slate-500 mt-1">
                            <span class="font-medium {{ $incident->impact === 'minor' ? 'text-yellow-600' : ($incident->impact === 'major' ? 'text-red-600' : 'text-slate-600') }}">
                                {{ ucfirst($incident->impact) }}
                            </span>
                            - Résolu en {{ $incident->occurred_at->diffForHumans($incident->resolved_at, true) }}
                        </div>
                        <div class="mt-3 text-slate-600 text-sm bg-slate-50 p-3 rounded border border-slate-100">
                            {{-- On affiche le dernier message de résolution --}}
                            {{ $incident->updates->first()->message ?? 'Incident résolu.' }}
                        </div>
                    </div>
                @empty
                    <div class="pl-8 text-slate-500 italic">Aucun incident significatif au cours des 90 derniers jours.</div>
                @endforelse
            </div>
        </div>

        {{-- FOOTER LINK --}}
        <div class="text-center text-sm text-slate-400">
            <a href="#" class="hover:text-ovh-primary">Politique de confidentialité</a> &bull; <a href="#" class="hover:text-ovh-primary">Support</a>
        </div>

    </div>

    {{-- MODAL SUBSCRIPTION --}}
    @if($showSubscribeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" wire:click="$set('showSubscribeModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-ovh-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                    S'abonner aux alertes
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">
                                        Recevez un email dès qu'un incident est créé, mis à jour ou résolu.
                                    </p>
                                    <div class="mt-4">
                                        <label for="email" class="block text-sm font-medium text-slate-700">Adresse email</label>
                                        <input type="email" wire:model="subscriberEmail" id="email" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-ovh-primary focus:ring focus:ring-ovh-primary/50" placeholder="vous@exemple.com">
                                        @error('subscriberEmail') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="subscribe" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-ovh-primary text-base font-medium text-white hover:bg-ovh-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ovh-primary sm:ml-3 sm:w-auto sm:text-sm">
                            S'inscrire
                        </button>
                        <button type="button" wire:click="$set('showSubscribeModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ovh-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
