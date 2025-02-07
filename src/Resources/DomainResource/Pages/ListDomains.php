<?php

namespace Backstage\Resources\DomainResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Backstage\Resources\DomainResource;

class ListDomains extends ListRecords
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
