<?php

namespace Backstage\Resources\TemplateResource\Pages;

use Backstage\Resources\TemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        dd($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        dd($data);

        return static::getModel()::create($data);
    }
}
