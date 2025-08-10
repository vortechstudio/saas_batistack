<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\License;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Support\Collection;

class GlobalSearchComponent extends Component
{
    public string $search = '';
    public array $results = [];
    public array $searchHistory = [];
    public bool $showResults = false;
    public int $selectedIndex = -1;
    
    protected $listeners = ['focusSearch' => 'focusSearch'];
    
    public function mount()
    {
        $this->searchHistory = session('search_history', []);
    }
    
    public function updatedSearch()
    {
        $this->selectedIndex = -1;
        
        if (strlen($this->search) < 2) {
            $this->results = [];
            $this->showResults = false;
            return;
        }
        
        $this->performSearch();
        $this->showResults = true;
    }
    
    public function performSearch()
    {
        $query = $this->search;
        $results = [];
        
        // Recherche dans les clients
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('company', 'like', "%{$query}%")
            ->limit(5)
            ->get();
            
        foreach ($customers as $customer) {
            $results[] = [
                'type' => 'customer',
                'title' => $customer->name,
                'subtitle' => $customer->email,
                'description' => $customer->company,
                'url' => route('filament.admin.resources.customers.view', $customer),
                'icon' => 'heroicon-o-user',
                'color' => 'blue',
            ];
        }
        
        // Recherche dans les licences
        $licenses = License::with(['customer', 'product'])
            ->where('license_key', 'like', "%{$query}%")
            ->orWhereHas('customer', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orWhereHas('product', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();
            
        foreach ($licenses as $license) {
            $results[] = [
                'type' => 'license',
                'title' => $license->license_key,
                'subtitle' => $license->customer->name,
                'description' => $license->product->name,
                'url' => route('filament.admin.resources.licenses.view', $license),
                'icon' => 'heroicon-o-key',
                'color' => 'green',
            ];
        }
        
        // Recherche dans les produits
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(3)
            ->get();
            
        foreach ($products as $product) {
            $results[] = [
                'type' => 'product',
                'title' => $product->name,
                'subtitle' => 'Produit',
                'description' => $product->description,
                'url' => route('filament.admin.resources.products.view', $product),
                'icon' => 'heroicon-o-cube',
                'color' => 'purple',
            ];
        }
        
        // Recherche par patterns spéciaux
        if (preg_match('/^#(\d+)$/', $query, $matches)) {
            $id = $matches[1];
            $this->searchById($id, $results);
        }
        
        if (filter_var($query, FILTER_VALIDATE_EMAIL)) {
            $this->searchByEmail($query, $results);
        }
        
        // Suggestions rapides
        if (empty($results)) {
            $results = $this->getQuickSuggestions($query);
        }
        
        $this->results = array_slice($results, 0, 10);
    }
    
    protected function searchById(int $id, array &$results)
    {
        // Recherche par ID dans toutes les tables
        $customer = Customer::find($id);
        if ($customer) {
            $results[] = [
                'type' => 'customer',
                'title' => "Client #{$id}: {$customer->name}",
                'subtitle' => $customer->email,
                'description' => 'Trouvé par ID',
                'url' => route('filament.admin.resources.customers.view', $customer),
                'icon' => 'heroicon-o-user',
                'color' => 'blue',
            ];
        }
        
        $license = License::with(['customer', 'product'])->find($id);
        if ($license) {
            $results[] = [
                'type' => 'license',
                'title' => "Licence #{$id}: {$license->license_key}",
                'subtitle' => $license->customer->name,
                'description' => 'Trouvé par ID',
                'url' => route('filament.admin.resources.licenses.view', $license),
                'icon' => 'heroicon-o-key',
                'color' => 'green',
            ];
        }
    }
    
    protected function searchByEmail(string $email, array &$results)
    {
        $customer = Customer::where('email', $email)->first();
        if ($customer) {
            $results[] = [
                'type' => 'customer',
                'title' => $customer->name,
                'subtitle' => $email,
                'description' => 'Trouvé par email',
                'url' => route('filament.admin.resources.customers.view', $customer),
                'icon' => 'heroicon-o-user',
                'color' => 'blue',
            ];
        }
    }
    
    protected function getQuickSuggestions(string $query): array
    {
        $suggestions = [];
        
        // Suggestions basées sur des mots-clés
        $keywords = [
            'expired' => [
                'title' => 'Licences expirées',
                'subtitle' => 'Voir toutes les licences expirées',
                'url' => route('filament.admin.resources.licenses.index', ['tableFilters[expired][value]' => true]),
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => 'red',
            ],
            'expiring' => [
                'title' => 'Licences qui expirent bientôt',
                'subtitle' => 'Voir les licences qui expirent dans 30 jours',
                'url' => route('filament.admin.resources.licenses.index', ['tableFilters[expires_soon][value]' => true]),
                'icon' => 'heroicon-o-clock',
                'color' => 'orange',
            ],
            'new' => [
                'title' => 'Nouveaux clients',
                'subtitle' => 'Voir les clients récents',
                'url' => route('filament.admin.resources.customers.index', ['tableFilters[new_this_week][value]' => true]),
                'icon' => 'heroicon-o-user-plus',
                'color' => 'green',
            ],
        ];
        
        foreach ($keywords as $keyword => $suggestion) {
            if (stripos($keyword, $query) !== false) {
                $suggestions[] = array_merge($suggestion, ['type' => 'suggestion']);
            }
        }
        
        return $suggestions;
    }
    
    public function selectResult(int $index)
    {
        if (isset($this->results[$index])) {
            $result = $this->results[$index];
            $this->addToHistory($this->search);
            return redirect($result['url']);
        }
    }
    
    public function navigateResults(string $direction)
    {
        $maxIndex = count($this->results) - 1;
        
        if ($direction === 'down') {
            $this->selectedIndex = min($this->selectedIndex + 1, $maxIndex);
        } elseif ($direction === 'up') {
            $this->selectedIndex = max($this->selectedIndex - 1, -1);
        }
    }
    
    public function selectCurrentResult()
    {
        if ($this->selectedIndex >= 0 && isset($this->results[$this->selectedIndex])) {
            return $this->selectResult($this->selectedIndex);
        }
    }
    
    public function clearSearch()
    {
        $this->search = '';
        $this->results = [];
        $this->showResults = false;
        $this->selectedIndex = -1;
    }
    
    public function focusSearch()
    {
        $this->dispatch('focus-search-input');
    }
    
    protected function addToHistory(string $query)
    {
        $history = array_filter($this->searchHistory, fn($item) => $item !== $query);
        array_unshift($history, $query);
        $this->searchHistory = array_slice($history, 0, 10);
        session(['search_history' => $this->searchHistory]);
    }
    
    public function render()
    {
        return view('livewire.global-search-component');
    }
}