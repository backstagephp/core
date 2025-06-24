<?php

namespace Backstage\Resources\BlockResource\Pages;

use Filament\Actions\CreateAction;
use Backstage\Resources\BlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlocks extends ListRecords
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
