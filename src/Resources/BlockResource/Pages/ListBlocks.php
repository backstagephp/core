<?php

namespace Vormkracht10\Backstage\Resources\BlockResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Resources\BlockResource;

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
