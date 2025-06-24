<?php

namespace Backstage\Resources\MenuItemResource\Pages;

use Filament\Actions\DeleteAction;
use Backstage\Resources\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
