<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NotificationCenterWidget extends BaseWidget
{
    protected static ?string $heading = 'Centre de Notifications';
    protected static ?string $description = 'Alertes et notifications importantes';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Notification::query()
                    ->where('notifiable_type', 'App\Models\User')
                    ->where('notifiable_id', Auth::user()->id)
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\IconColumn::make('type')
                    ->label('')
                    ->icon(fn ($record) => $record->getTypeIcon())
                    ->color(fn ($record) => $record->getTypeColor())
                    ->size('lg'),

                Tables\Columns\TextColumn::make('data.title')
                    ->label('Notification')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->data['message'] ?? ''),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçue')
                    ->since()
                    ->sortable(),

                Tables\Columns\IconColumn::make('read_at')
                    ->label('Lu')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->recordActions([
                Action::make('markAsRead')
                    ->label('Marquer comme lu')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->isUnread())
                    ->action(fn ($record) => $record->markAsRead()),

                Action::make('markAsUnread')
                    ->label('Marquer comme non lu')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn ($record) => $record->isRead())
                    ->action(fn ($record) => $record->markAsUnread()),

                Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => $record->data['action_url'] ?? '#')
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkAction::make('markAllAsRead')
                    ->label('Marquer tout comme lu')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($records) => $records->each->markAsRead()),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->poll('30s')
            ->emptyStateHeading('Aucune notification')
            ->emptyStateDescription('Vous êtes à jour !')
            ->emptyStateIcon('heroicon-o-bell-slash');
    }
}
