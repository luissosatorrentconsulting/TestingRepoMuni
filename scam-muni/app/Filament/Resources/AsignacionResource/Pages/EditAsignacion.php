<?php

namespace App\Filament\Resources\AsignacionResource\Pages;

use App\Filament\Resources\AsignacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsignacion extends EditRecord
{
    protected static string $resource = AsignacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Blindaje del lado del servidor: ni el responsable ni el bien se
        // cambian desde "Corregir" bajo ningún caso, aunque los campos estén
        // deshabilitados en el formulario. Para eso está "Reasignar".
        $data['empleado_id'] = $this->record->empleado_id;
        $data['activo_id'] = $this->record->activo_id;

        return $data;
    }

    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
}
