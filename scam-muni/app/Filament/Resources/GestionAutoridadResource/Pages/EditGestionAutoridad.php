<?php

namespace App\Filament\Resources\GestionAutoridadResource\Pages;

use App\Filament\Resources\GestionAutoridadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGestionAutoridad extends EditRecord
{
    protected static string $resource = GestionAutoridadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
