<?php

namespace Backstage\Resources\SettingResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Backstage\Resources\SettingResource;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
