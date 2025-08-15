<?php

namespace App\Filament\Pages;

use App\Models\Notification;
use App\Enums\NotificationType;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;

class NotificationCenter extends Page implements HasTable, HasActions
{
    use InteractsWithTable, InteractsWithActions;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Centre de Notifications';
    protected static ?string $title = 'Centre de Notifications';
    protected static ?int $navigationSort = 9;
    protected string $view = 'filament.pages.notification-center';

    // SUPPRIMER cette ligne problématique :
    // protected string $view = 'filament-panels::page';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Notification::query()
                    ->where('notifiable_type', 'App\Models\User')
                    ->where('notifiable_id', Auth::user()->id)
                    ->latest()
            )
            ->columns([
                Tables\Columns\IconColumn::make('type')
                    ->label('')
                    ->icon(fn ($record) => $record->getTypeIcon())
                    ->color(fn ($record) => $record->getTypeColor())
                    ->size('sm'),

                Tables\Columns\TextColumn::make('data.title')
                    ->label('Notification')
                    ->searchable()
                    ->weight(fn ($record) => $record->isUnread() ? 'bold' : 'normal')
                    ->description(fn ($record) => $record->data['message'] ?? '')
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                Tables\Columns\TextColumn::make('data.priority')
                    ->label('Priorité')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'critical' => 'Critique',
                        'high' => 'Haute',
                        'medium' => 'Moyenne',
                        'low' => 'Basse',
                        default => 'Normale',
                    })
                    ->color(fn ($state) => match($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçue')
                    ->since()
                    ->sortable(),

                Tables\Columns\IconColumn::make('read_at')
                    ->label('Statut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn ($record) => $record->isRead() ? 'Lu' : 'Non lu'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(collect(NotificationType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])),

                Tables\Filters\SelectFilter::make('read_status')
                    ->label('Statut')
                    ->options([
                        'unread' => 'Non lues',
                        'read' => 'Lues',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'unread') {
                            return $query->whereNull('read_at');
                        } elseif ($data['value'] === 'read') {
                            return $query->whereNotNull('read_at');
                        }
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Priorité')
                    ->options([
                        'critical' => 'Critique',
                        'high' => 'Haute',
                        'medium' => 'Moyenne',
                        'low' => 'Basse',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            return $query->whereJsonContains('data->priority', $data['value']);
                        }
                        return $query;
                    }),
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
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => isset($record->data['action_url'])),

                DeleteAction::make()
                    ->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkAction::make('markAllAsRead')
                    ->label('Marquer comme lu')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($records) => $records->each->markAsRead()),

                BulkAction::make('markAllAsUnread')
                    ->label('Marquer comme non lu')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->action(fn ($records) => $records->each->markAsUnread()),

                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAllAsRead')
                ->label('Tout marquer comme lu')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    Auth::user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
                    $this->dispatch('$refresh');
                }),

            Action::make('deleteAllRead')
                ->label('Supprimer toutes les lues')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    Auth::user()->notifications()->whereNotNull('read_at')->delete();
                    $this->dispatch('$refresh');
                }),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Auth::user()?->notifications()->whereNull('read_at')->count() ?? 0;
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $highPriorityCount = Auth::user()?->notifications()
            ->whereNull('read_at')
            ->whereIn('type', [
                NotificationType::SECURITY_ALERT,
                NotificationType::SYSTEM_ALERT,
                NotificationType::LICENSE_EXPIRED,
            ])
            ->count() ?? 0;
        return $highPriorityCount > 0 ? 'danger' : 'primary';
    }
}
