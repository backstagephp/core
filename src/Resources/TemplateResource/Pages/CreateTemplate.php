<?php

namespace Vormkracht10\Backstage\Resources\TemplateResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vormkracht10\Backstage\Resources\TemplateResource;
use Illuminate\Database\Eloquent\Model;

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