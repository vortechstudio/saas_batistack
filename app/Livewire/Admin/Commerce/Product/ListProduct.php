<?php

namespace App\Livewire\Admin\Commerce\Product;

use App\Filament\Imports\ProductImporter;
use App\Models\Product\Product;
use App\Trait\Product\ProductSchema;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ListProduct extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions, InteractsWithSchemas, InteractsWithTable, ProductSchema;

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::with('features')->newQuery())
            ->heading("Liste des produits")
            ->emptyStateHeading("Aucun produit dans la base")
            ->emptyStateActions([
                CreateAction::make('create')
                    ->label("Ajouter un produit")
                    ->icon(Heroicon::PlusCircle)
                    ->modalHeading("Ajouter un produit")
                    ->modalIcon(Heroicon::PlusCircle)
                    ->schema($this->getSchemaProduct())
                    ->using(function (array $data) {

                    }),
            ])
            ->columns([
                ImageColumn::make('media')
                    ->label('')
                    ->width('50px'),

                TextColumn::make('name')
                    ->label("Produit")
                    ->description(fn (?Model $record) => \Str::limit($record->description, 50)),

                TextColumn::make('category')
                    ->label("Catégorie")
                    ->formatStateUsing(fn (?Model $record) => $record->category->label()),

                IconColumn::make('active')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                TernaryFilter::make('active'),
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->label("Ajouter un produit")
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->modalHeading("Ajouter un produit")
                    ->modalIcon(Heroicon::PlusCircle)
                    ->modalWidth(Width::Full)
                    ->schema($this->getSchemaProduct())
                    ->using(function (array $data) {
                        dd($data);
                    }),
            ])
            ->recordActions([]);
    }

    public function render()
    {
        return view('livewire.admin.commerce.product.list-product');
    }
}
