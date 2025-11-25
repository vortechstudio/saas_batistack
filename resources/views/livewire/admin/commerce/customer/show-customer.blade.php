<div>
    <x-mary-tabs wire:model="selectedTab" active-class="text-primary bg-blue-100 rounded-md">
        <x-mary-tab name="tiers" label="Client" icon="o-users">
            @livewire('admin.commerce.components.tabs.client-tab', ['customer' => $customer])
        </x-mary-tab>
        <x-mary-tab name="services" label="Services" icon="o-server">
            @livewire('admin.commerce.components.tabs.client-tab', ['customer' => $customer])
            @livewire('admin.commerce.components.tabs.service-tab', ['customer' => $customer])
        </x-mary-tab>
        <x-mary-tab name="orders" label="Commandes & Facturations" icon="o-shopping-cart">
            Contentent
        </x-mary-tab>
        <x-mary-tab name="supports" label="Support" icon="o-lifebuoy">
            Contentent
        </x-mary-tab>
    </x-mary-tabs>
</div>
