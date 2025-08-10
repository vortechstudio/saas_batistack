<?php

namespace App\Filament\Widgets;

use App\Models\Module;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class PopularModulesWidget extends BaseWidget
{
    protected static ?string $heading = 'Modules Populaires';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Module::query()
                    ->select('modules.*')
                    ->selectSub(
                        DB::table('license_modules')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('license_modules.module_id', 'modules.id')
                            ->where('license_modules.enabled', true),
                        'licenses_count'
                    )
                    ->where('is_active', true)
                    ->orderBy('licenses_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Module')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match($state->value) {
                        'core' => 'success',
                        'addon' => 'warning',
                        'premium' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Prix')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('licenses_count')
                    ->label('Utilisations')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    }),
            ])
            ->defaultSort('licenses_count', 'desc')
            ->paginated(false);
    }
}
