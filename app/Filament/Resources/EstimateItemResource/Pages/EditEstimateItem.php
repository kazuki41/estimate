<?php

namespace App\Filament\Resources\EstimateItemResource\Pages;

use App\Filament\Resources\EstimateItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEstimateItem extends EditRecord
{
    protected static string $resource = EstimateItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
