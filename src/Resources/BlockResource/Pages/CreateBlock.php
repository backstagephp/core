<?php

namespace Backstage\Resources\BlockResource\Pages;

use Backstage\Resources\BlockResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateBlock extends CreateRecord
{
    protected static string $resource = BlockResource::class;

    protected function afterCreate(): void
    {
        $this->record->sites()->attach(Filament::getTenant());
    }
}
