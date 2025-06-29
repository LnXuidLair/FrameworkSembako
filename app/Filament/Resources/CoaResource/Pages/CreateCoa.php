<?php

namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCoa extends CreateRecord
{
    protected static string $resource = CoaResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirect ke halaman utama COA
    }
}