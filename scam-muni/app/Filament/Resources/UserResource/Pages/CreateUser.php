<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Un admin de municipalidad SIEMPRE crea usuarios de su propia
        // municipalidad, sin importar lo que llegue en el formulario (el
        // campo ni siquiera se le muestra, pero esto lo blinda del lado
        // del servidor). Solo un Super Admin puede elegir otra o dejarla
        // vacía para crear otro Super Admin.
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
