<?php

namespace Vormkracht10\Backstage\Resources\MenuItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Vormkracht10\Backstage\Resources\MenuResource;

class ListMenuItems extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
