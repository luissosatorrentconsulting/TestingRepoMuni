<?php

namespace App\Filament\Resources\BienVarioResource\Pages;

use App\Filament\Resources\BienVarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBienVario extends EditRecord
{
    protected static string $resource = BienVarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
