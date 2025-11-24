<div class="bg-white min-h-screen pb-24 font-sans text-slate-600" wire:poll.60s>

    {{-- TOP NAVIGATION (Style épuré StatusPage) --}}
    <div class="border-b border-slate-200 bg-white sticky top-0 z-50 shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex-shrink-0">
                    <x-app-logo class="h-7 w-auto text-ovh-primary" />
                </a>
                <div class="h-6 w-px bg-slate-200 mx-2 hidden sm:block"></div>
                <span class="text-lg font-semibold text-slate-800 tracking-tight hidden sm:block">Statut des services</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('assistance.index') }}" class="text-sm font-medium text-slate-500 hover:text-ovh-primary hidden md:block">Support</a>
                <button wire:click="$set('showSubscribeModal', true)" class="bg-ovh-primary text-white px-4 py-2 rounded text-sm font-bold hover:bg-ovh-dark transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    S'abonner
                </button>
            </div>
        </div>
    </div>

    {{-- NOTIFICATION FLASH --}}
    @if (session('status_success'))
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 flex items-center">
                <svg class="h-5 w-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <p class="text-sm font-medium">{{ session('status_success') }}</p>
            </div>
        </div>
    @endif

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">

        {{-- GLOBAL STATUS INDICATOR --}}
        <div class="{{ $this->globalStatus['color'] }} rounded-t-lg p-6 text-white shadow-sm flex flex-col md:flex-row items-center justify-between text-center md:text-left transition-colors duration-500">
            <div class="flex items-center gap-4 mb-4 md:mb-0">
                @if($this->globalStatus['icon'] === 'check-circle')
                    <svg class="w-12 h-12 flex-shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @else
                    <svg class="w-12 h-12 flex-shrink-0 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                @endif
                <div>
                    <span class="text-2xl font-bold block tracking-tight">{{ $this->globalStatus['label'] }}</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-white/80 text-sm font-medium">Dernière mise à jour</div>
                <div class="text-white font-bold">{{ now()->format('H:i:s') }}</div>
            </div>
        </div>

        {{-- UPTIME HISTORY BARS (LA TOUCHE PRO) --}}
        <div class="bg-white border-x border-b border-slate-200 rounded-b-lg p-6 mb-12 shadow-sm">
            <div class="flex justify-between items-end mb-3">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Disponibilité système (60 jours)</h3>
                <span class="text-xs text-green-600 font-bold bg-green-50 px-2 py-1 rounded border border-green-100">100% Uptime</span>
            </div>

            {{-- The Bars Container --}}
            <div class="flex gap-[3px] h-10 w-full items-end">
                @foreach($uptimeHistory as $day)
                    <div
                        class="flex-1 rounded-[1px] cursor-help transition-all duration-200 hover:opacity-80 relative group
                        {{ $day['status'] === 'operational' ? 'bg-green-500 h-8 hover:h-10' : ($day['impact'] === 'critical' ? 'bg-red-500 h-10' : 'bg-orange-400 h-10') }}"
                    >
                        {{-- Custom Tooltip --}}
                        <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 hidden group-hover:block bg-slate-900 text-white text-xs font-medium rounded py-1.5 px-3 whitespace-nowrap z-20 shadow-xl">
                            {{ $day['tooltip'] }}
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between mt-3 text-xs text-slate-400 font-medium border-t border-slate-100 pt-2">
                <span>Il y a 60 jours</span>
                <span>Aujourd'hui</span>
            </div>
        </div>

        {{-- ACTIVE INCIDENTS --}}
        @if($activeIncidents->count() > 0)
            <div class="mb-12">
                <h2 class="text-xl font-bold text-slate-900 mb-4">Incidents en cours</h2>
                <div class="space-y-6">
                    @foreach($activeIncidents as $incident)
                        <div class="bg-white rounded border border-orange-200 shadow-sm overflow-hidden">
                            <div class="bg-orange-50/50 px-6 py-4 border-b border-orange-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-orange-800">{{ $incident->title }}</h3>
                                <span class="text-xs font-bold uppercase tracking-wider text-white bg-orange-500 px-2 py-1 rounded">{{ $incident->status->getLabel() }}</span>
                            </div>
                            <div class="p-6">
                                <div class="mb-6 text-sm text-slate-600">
                                    <strong>Services impactés :</strong>
                                    @foreach($incident->components as $comp)
                                        <span class="text-slate-800 font-medium">{{ $comp->name }}</span>{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                </div>
                                <div class="space-y-6 relative pl-4 border-l-2 border-slate-200 ml-2">
                                    @foreach($incident->updates as $update)
                                        <div class="relative group">
                                            <div class="absolute -left-[21px] top-1.5 w-3 h-3 rounded-full bg-slate-300 ring-4 ring-white group-first:bg-orange-500"></div>
                                            <div class="text-sm font-bold text-slate-800 mb-1">
                                                {{ $update->status->getLabel() }}
                                            </div>
                                            <div class="text-slate-700 text-sm leading-relaxed">{!! nl2br(e($update->message)) !!}</div>
                                            <div class="text-xs text-slate-400 mt-2">{{ $update->created_at->translatedFormat('d M, H:i') }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- SYSTEM COMPONENTS LIST (Compact Table) --}}
        <div class="mb-16">
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                @foreach($groups as $groupName => $components)
                    <div class="border-b border-slate-200 last:border-0">
                        <div class="bg-slate-50 px-5 py-3 border-b border-slate-200/60 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wide">{{ $groupName ?: 'Services Généraux' }}</h3>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @foreach($components as $component)
                                <div class="px-5 py-3 flex items-center justify-between hover:bg-slate-50/80 transition group">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-slate-700">{{ $component->name }}</span>
                                        @if($component->description)
                                            <div class="relative ml-2" x-data="{ tooltip: false }">
                                                <svg @mouseenter="tooltip = true" @mouseleave="tooltip = false" class="w-4 h-4 text-slate-300 cursor-help hover:text-slate-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div x-show="tooltip" class="absolute left-6 top-0 bg-slate-800 text-white text-xs rounded p-2 w-56 z-10 shadow-lg" style="display: none;">
                                                    {{ $component->description }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- STATUS BADGE ALIGNÉ À DROITE --}}
                                    <div class="flex items-center">
                                        @if($component->status->value === 'operational')
                                            <span class="text-green-600 font-bold text-xs uppercase tracking-wide">Opérationnel</span>
                                            <svg class="w-5 h-5 text-green-500 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        @elseif($component->status->value === 'performance_issues')
                                            <span class="text-blue-600 font-bold text-xs uppercase tracking-wide">Dégradé</span>
                                            <svg class="w-5 h-5 text-blue-500 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                        @elseif($component->status->value === 'maintenance')
                                            <span class="text-blue-600 font-bold text-xs uppercase tracking-wide">Maintenance</span>
                                            <svg class="w-5 h-5 text-blue-500 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
                                        @else
                                            <span class="text-red-600 font-bold text-xs uppercase tracking-wide">Panne</span>
                                            <svg class="w-5 h-5 text-red-500 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PAST INCIDENTS (Timeline Verticale Épurée) --}}
        <div class="mb-16">
            <h2 class="text-xl font-bold text-slate-900 mb-6 pb-2 border-b border-slate-200">Historique des incidents</h2>

            <div class="space-y-10 pl-2">
                @forelse($pastIncidents as $incident)
                    <div class="group">
                        <div class="flex items-baseline gap-4 mb-2">
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-ovh-primary transition-colors">{{ $incident->title }}</h3>
                            <span class="text-xs text-slate-400">{{ $incident->occurred_at->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="pl-4 border-l-2 border-slate-200 ml-1 space-y-2 py-1">
                            <div class="text-sm text-slate-600">
                                <span class="font-semibold text-slate-700">Impact:</span>
                                <span class="{{ $incident->impact === 'minor' ? 'text-yellow-600' : ($incident->impact === 'major' ? 'text-red-600' : 'text-slate-500') }}">
                                    {{ ucfirst($incident->impact) }}
                                </span>
                            </div>
                            <div class="text-sm text-slate-600">
                                <span class="font-semibold text-slate-700">Résolu en:</span> {{ $incident->occurred_at->diffForHumans($incident->resolved_at, true) }}
                            </div>
                            <div class="text-sm text-slate-500 italic mt-2">
                                "{{ $incident->updates->first()->message ?? 'Incident résolu.' }}"
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-slate-500 text-sm">Aucun incident majeur au cours des 90 derniers jours.</div>
                @endforelse
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="border-t border-slate-200 pt-8 text-center flex justify-center gap-6 text-sm text-slate-400 pb-8">
            <a href="{{ route('home') }}" class="hover:text-ovh-primary">Batistack</a>
            <a href="#" class="hover:text-ovh-primary">Support</a>
            <a href="#" class="hover:text-ovh-primary">Twitter</a>
        </div>

    </div>

    {{-- MODAL SUBSCRIPTION (Identique mais style retouché) --}}
    @if($showSubscribeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" wire:click="$set('showSubscribeModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">S'abonner aux alertes</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-500">Recevez un email instantané dès qu'un incident est créé.</p>
                                    <div class="mt-4">
                                        <input type="email" wire:model="subscriberEmail" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-ovh-primary focus:ring focus:ring-ovh-primary/50" placeholder="email@entreprise.com">
                                        @error('subscriberEmail') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="subscribe" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-slate-900 text-base font-medium text-white hover:bg-slate-800 focus:outline-none sm:w-auto sm:text-sm">S'inscrire</button>
                        <button type="button" wire:click="$set('showSubscribeModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
