<?php

namespace App\Filament\Resources\PuestoResource\Pages;

use App\Filament\Resources\PuestoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPuesto extends EditRecord
{
    protected static string $resource = PuestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
}
