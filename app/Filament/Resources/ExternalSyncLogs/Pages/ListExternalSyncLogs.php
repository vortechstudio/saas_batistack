<?php

namespace App\Filament\Resources\ExternalSyncLogs\Pages;

use App\Filament\Resources\ExternalSyncLogs\ExternalSyncLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalSyncLogs extends ListRecords
{
    protected static string $resource = ExternalSyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
