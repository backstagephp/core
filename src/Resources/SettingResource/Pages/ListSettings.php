<?php

namespace Backstage\Resources\SettingResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Backstage\Resources\SettingResource;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
