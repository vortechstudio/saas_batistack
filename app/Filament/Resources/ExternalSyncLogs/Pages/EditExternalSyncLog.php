<?php

namespace App\Filament\Resources\ExternalSyncLogs\Pages;

use App\Filament\Resources\ExternalSyncLogs\ExternalSyncLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalSyncLog extends EditRecord
{
    protected static string $resource = ExternalSyncLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
