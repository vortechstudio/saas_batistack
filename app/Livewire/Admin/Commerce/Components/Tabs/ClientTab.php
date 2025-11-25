<?php

namespace App\Livewire\Admin\Commerce\Components\Tabs;

use App\Models\Customer\Customer;
use App\Trait\Commerce\TiersSchema;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ClientTab extends Component implements HasSchemas, HasActions
{
    use InteractsWithSchemas, InteractsWithActions, TiersSchema;

    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }


    public function render()
    {
        return view('livewire.admin.commerce.components.tabs.client-tab');
    }
}
