<?php

namespace App\Filament\Resources\AcademiaResource\Pages;

use App\Filament\Resources\AcademiaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAcademias extends ListRecords
{
    protected static string $resource = AcademiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
