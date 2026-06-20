<?php

namespace App\Filament\Resources\BienVarioResource\Pages;

use App\Filament\Resources\BienVarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBienVarios extends ListRecords
{
    protected static string $resource = BienVarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
