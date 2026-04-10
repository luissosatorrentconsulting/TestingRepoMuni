<?php

namespace App\Filament\Resources\AsignacionResource\Pages;

use App\Filament\Resources\AsignacionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAsignacion extends CreateRecord
{
    protected static string $resource = AsignacionResource::class;
    protected function getRedirectUrl(): string
{
    return $this->getResource()::getUrl('index');
}
}
