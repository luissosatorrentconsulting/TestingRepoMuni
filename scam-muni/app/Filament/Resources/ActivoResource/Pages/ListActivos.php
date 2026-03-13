<?php

namespace App\Filament\Resources\ActivoResource\Pages;

use App\Filament\Resources\ActivoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivos extends ListRecords
{
    protected static string $resource = ActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
