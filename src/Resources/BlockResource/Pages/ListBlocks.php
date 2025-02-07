<?php

namespace Backstage\Resources\BlockResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Backstage\Resources\BlockResource;

class ListBlocks extends ListRecords
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
