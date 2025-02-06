<?php

namespace Backstage\Resources\TemplateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Backstage\Resources\TemplateResource;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
