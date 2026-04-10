<?php

namespace App\Filament\Resources\PuestoResource\Pages;

use App\Filament\Resources\PuestoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePuesto extends CreateRecord
{
    protected static string $resource = PuestoResource::class;
    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
}
