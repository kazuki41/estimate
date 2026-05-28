<?php

namespace App\Filament\Resources\EstimateItemResource\Pages;

use App\Filament\Resources\EstimateItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstimateItems extends ListRecords
{
    protected static string $resource = EstimateItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
