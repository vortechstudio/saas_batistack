<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Table des notifications -->
        {{ $this->table }}
    </div>

    <script>
        // Auto-refresh toutes les 30 secondes
        setInterval(() => {
            @this.dispatch('$refresh');
        }, 30000);
    </script>
</x-filament-panels::page>
