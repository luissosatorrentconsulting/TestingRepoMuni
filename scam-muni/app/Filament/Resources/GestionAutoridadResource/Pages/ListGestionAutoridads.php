<?php

namespace App\Filament\Resources\GestionAutoridadResource\Pages;

use App\Filament\Resources\GestionAutoridadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGestionAutoridads extends ListRecords
{
    protected static string $resource = GestionAutoridadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
