<?php

namespace App\Livewire\Client\Account\Components\Table;

use App\Models\Customer\CustomerService;
use App\Services\TenantApiService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ServiceJournalTable extends Component implements HasTable, HasSchemas, HasActions
{
    use InteractsWithTable, InteractsWithSchemas, InteractsWithActions;

    public CustomerService $service;

    public function mount(CustomerService $service)
    {
        $this->service = $service;
    }

    public function table(Table $table): Table
    {
        $response = app(TenantApiService::class)->for($this->service)->getActivityLog()->collect();
        return $table
            ->records(fn () => $response->toArray())
            ->columns([

            ]);
    }

    public function render()
    {
        return view('livewire.client.account.components.table.service-journal-table');
    }
}
