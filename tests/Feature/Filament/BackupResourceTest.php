<?php

use App\Models\Backup;
use App\Models\User;
use App\Enums\BackupStatus;
use App\Filament\Resources\Backups\BackupResource;
use App\Filament\Resources\Backups\Pages\ListBackups;
use App\Filament\Resources\Backups\Pages\CreateBackup;
use App\Filament\Resources\Backups\Pages\EditBackup;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'admin@batistack.com',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($this->user);
});

describe('Backup Resource', function () {
    test('can render backup list page', function () {
        $this->get(BackupResource::getUrl('index'))
            ->assertSuccessful();
    });

    test('can list backups', function () {
        $backups = Backup::factory()->count(10)->create();

        livewire(ListBackups::class)
            ->assertCanSeeTableRecords($backups);
    });

    test('can render backup create page', function () {
        $this->get(BackupResource::getUrl('create'))
            ->assertSuccessful();
    });

    test('can create backup', function () {
        $newData = Backup::factory()->make();

        livewire(CreateBackup::class)
            ->fillForm([
                'name' => $newData->name,
                'type' => $newData->type->value,
                'storage_driver' => $newData->storage_driver,
                'file_path' => $newData->file_path,
                'status' => BackupStatus::PENDING->value,
                'file_size' => $newData->file_size,
                'started_at' => $newData->started_at->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Backup::class, [
            'name' => $newData->name,
            'type' => $newData->type->value,
            'storage_driver' => $newData->storage_driver,
            'file_path' => $newData->file_path,
        ]);
    });

    test('can validate backup creation', function () {
        livewire(CreateBackup::class)
            ->fillForm([
                'name' => null,
                'type' => null,
                'storage_driver' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'type' => 'required',
                'storage_driver' => 'required',
            ]);
    });

    test('can render backup edit page', function () {
        $backup = Backup::factory()->create();

        $this->get(BackupResource::getUrl('edit', [
            'record' => $backup,
        ]))->assertSuccessful();
    });

    test('can retrieve backup data for editing', function () {
        $backup = Backup::factory()->create();

        livewire(EditBackup::class, [
            'record' => $backup->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $backup->name,
                'type' => $backup->type->value,
                'storage_driver' => $backup->storage_driver,
                'file_path' => $backup->file_path,
                'status' => $backup->status->value,
                'file_size' => $backup->file_size,
            ]);
    });

    test('can save backup', function () {
        $backup = Backup::factory()->create();
        $newData = Backup::factory()->make();

        livewire(EditBackup::class, [
            'record' => $backup->getRouteKey(),
        ])
            ->fillForm([
                'name' => $newData->name,
                'status' => BackupStatus::COMPLETED->value,
                'file_size' => $newData->file_size,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($backup->refresh())
            ->name->toBe($newData->name)
            ->status->toBe(BackupStatus::COMPLETED)
            ->file_size->toBe($newData->file_size);
    });

    test('can delete backup', function () {
        $backup = Backup::factory()->create();

        livewire(EditBackup::class, [
            'record' => $backup->getRouteKey(),
        ])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($backup);
    });

    test('can search backups', function () {
        $backups = Backup::factory()->count(10)->create();
        $searchBackup = $backups->first();

        livewire(ListBackups::class)
            ->searchTable($searchBackup->name)
            ->assertCanSeeTableRecords([$searchBackup])
            ->assertCanNotSeeTableRecords($backups->skip(1));
    });

    test('can sort backups', function () {
        $backups = Backup::factory()->count(10)->create();

        livewire(ListBackups::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($backups->sortBy('name'), inOrder: true)
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords($backups->sortByDesc('name'), inOrder: true);
    });



    test('can bulk delete backups', function () {
        $backups = Backup::factory()->count(10)->create();

        livewire(ListBackups::class)
            ->callTableBulkAction('delete', $backups);

        foreach ($backups as $backup) {
            $this->assertModelMissing($backup);
        }
    });

    test('displays navigation badge with backup count', function () {
        Backup::factory()->count(6)->create();

        expect(BackupResource::getNavigationBadge())->toBe('6');
    });

    test('can globally search backups', function () {
        $backup = Backup::factory()->create([
            'name' => 'Unique Backup Name',
        ]);

        $searchableAttributes = BackupResource::getGloballySearchableAttributes();

        expect($searchableAttributes)->toContain('name')
            ->and($searchableAttributes)->toContain('file_path');
    });
});
