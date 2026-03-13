<?php

namespace App\Filament\Resources\ActivoResource\Pages;

use App\Filament\Resources\ActivoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditActivo extends EditRecord
{
    protected static string $resource = ActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
