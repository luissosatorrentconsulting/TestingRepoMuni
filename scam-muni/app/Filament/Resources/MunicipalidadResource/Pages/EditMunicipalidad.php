<?php

namespace App\Filament\Resources\MunicipalidadResource\Pages;

use App\Filament\Resources\MunicipalidadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMunicipalidad extends EditRecord
{
    protected static string $resource = MunicipalidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
