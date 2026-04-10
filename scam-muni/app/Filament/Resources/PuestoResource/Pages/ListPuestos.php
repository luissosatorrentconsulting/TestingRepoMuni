<?php

namespace App\Filament\Resources\PuestoResource\Pages;

use App\Filament\Resources\PuestoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPuestos extends ListRecords
{
    protected static string $resource = PuestoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
