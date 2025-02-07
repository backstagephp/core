<?php

namespace Backstage\Resources\TypeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Backstage\Resources\TypeResource;

class ListTypes extends ListRecords
{
    protected static string $resource = TypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
