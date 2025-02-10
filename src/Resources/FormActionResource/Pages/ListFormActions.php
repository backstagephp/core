<?php

namespace Backstage\Resources\FormActionResource\Pages;

use Backstage\Resources\FormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormActions extends ListRecords
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
