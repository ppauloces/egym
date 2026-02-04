<?php

namespace App\Filament\Resources\AcademiaResource\Pages;

use App\Filament\Resources\AcademiaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAcademia extends EditRecord
{
    protected static string $resource = AcademiaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
