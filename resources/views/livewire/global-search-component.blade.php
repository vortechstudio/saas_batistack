<div class="relative" x-data="{ 
    showResults: @entangle('showResults'),
    selectedIndex: @entangle('selectedIndex')
}" 
x-on:keydown.escape="$wire.clearSearch()"
x-on:keydown.arrow-down.prevent="$wire.navigateResults('down')"
x-on:keydown.arrow-up.prevent="$wire.navigateResults('up')"
x-on:keydown.enter.prevent="$wire.selectCurrentResult()">

    <!-- Champ de recherche -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search"
            x-on:focus="showResults = true"
            x-on:focus-search-input.window="$el.focus()"
            placeholder="Rechercher clients, licences, produits... (/ pour focus)"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        />
        
        @if($search)
            <button 
                wire:click="clearSearch"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
                <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @endif
    </div>

    <!-- Résultats de recherche -->
    @if($showResults && (count($results) > 0 || $search))
        <div class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-96 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
            
            @if(count($results) > 0)
                @foreach($results as $index => $result)
                    <div 
                        wire:click="selectResult({{ $index }})"
                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-50 {{ $selectedIndex === $index ? 'bg-blue-50 text-blue-900' : 'text-gray-900' }}"
                    >
                        <div class="flex items-center">
                            <!-- Icône -->
                            <div class="flex-shrink-0 mr-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $result['color'] === 'blue' ? 'bg-blue-100 text-blue-600' : ($result['color'] === 'green' ? 'bg-green-100 text-green-600' : ($result['color'] === 'purple' ? 'bg-purple-100 text-purple-600' : ($result['color'] === 'red' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600'))) }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($result['icon'] === 'heroicon-o-user')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        @elseif($result['icon'] === 'heroicon-o-key')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        @elseif($result['icon'] === 'heroicon-o-cube')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        @elseif($result['icon'] === 'heroicon-o-exclamation-triangle')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        @elseif($result['icon'] === 'heroicon-o-clock')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @elseif($result['icon'] === 'heroicon-o-user-plus')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                        @endif
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Contenu -->
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate">{{ $result['title'] }}</div>
                                @if($result['subtitle'])
                                    <div class="text-sm text-gray-500 truncate">{{ $result['subtitle'] }}</div>
                                @endif
                                @if($result['description'])
                                    <div class="text-xs text-gray-400 truncate">{{ $result['description'] }}</div>
                                @endif
                            </div>
                            
                            <!-- Type badge -->
                            <div class="flex-shrink-0 ml-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $result['color'] === 'blue' ? 'bg-blue-100 text-blue-800' : ($result['color'] === 'green' ? 'bg-green-100 text-green-800' : ($result['color'] === 'purple' ? 'bg-purple-100 text-purple-800' : ($result['color'] === 'red' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))) }}">
                                    {{ ucfirst($result['type']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="relative cursor-default select-none py-2 px-4 text-gray-700">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-.9-6.134-2.379M15 13.5A7.962 7.962 0 0112 15c-2.34 0-4.5-.9-6.134-2.379M15 13.5l6.5 6.5M15 13.5L21 7"></path>
                        </svg>
                        <span>Aucun résultat trouvé pour "{{ $search }}"</span>
                    </div>
                </div>
            @endif
            
            <!-- Historique de recherche -->
            @if(empty($search) && count($searchHistory) > 0)
                <div class="border-t border-gray-200 mt-2 pt-2">
                    <div class="px-3 py-1 text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Recherches récentes
                    </div>
                    @foreach(array_slice($searchHistory, 0, 5) as $historyItem)
                        <div 
                            wire:click="$set('search', '{{ $historyItem }}')"
                            class="cursor-pointer select-none relative py-1 pl-3 pr-9 hover:bg-gray-50 text-gray-700"
                        >
                            <div class="flex items-center">
                                <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm">{{ $historyItem }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <!-- Aide -->
            <div class="border-t border-gray-200 mt-2 pt-2">
                <div class="px-3 py-1 text-xs text-gray-500">
                    <div class="flex justify-between">
                        <span>Conseils: #123 pour ID, email@domain.com pour email</span>
                        <span>↑↓ pour naviguer, ↵ pour sélectionner</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Raccourci clavier pour focus sur la recherche
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            Livewire.dispatch('focusSearch');
        }
    });
</script>