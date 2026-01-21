<?php

namespace Backstage\Resources\TemplateResource\Pages;

use Backstage\Resources\TemplateResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    protected function afterCreate(): void
    {
        $this->record->sites()->attach(Filament::getTenant());
    }
}
