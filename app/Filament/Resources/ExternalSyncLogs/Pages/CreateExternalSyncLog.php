<?php

namespace App\Filament\Resources\ExternalSyncLogs\Pages;

use App\Filament\Resources\ExternalSyncLogs\ExternalSyncLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExternalSyncLog extends CreateRecord
{
    protected static string $resource = ExternalSyncLogResource::class;
}
