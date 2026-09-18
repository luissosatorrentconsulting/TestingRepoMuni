<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Mismo blindaje que en CreateUser: un admin de municipalidad no
        // puede mover un usuario a otra municipalidad ni volverlo Super Admin.
        if (!auth()->user()?->isSuperAdmin()) {
            $data['municipalidad_id'] = auth()->user()?->municipalidad_id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
