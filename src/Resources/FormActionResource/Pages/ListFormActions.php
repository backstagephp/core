<?php

namespace Backstage\Resources\FormActionResource\Pages;

use Filament\Actions\CreateAction;
use Backstage\Resources\FormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormActions extends ListRecords
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
