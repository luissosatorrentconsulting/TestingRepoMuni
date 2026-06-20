<?php

namespace App\Filament\Resources\ControlObraResource\Pages;

use App\Filament\Resources\ControlObraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControlObra extends EditRecord
{
    protected static string $resource = ControlObraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
