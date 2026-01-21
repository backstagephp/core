<?php

namespace Backstage\Resources\TypeResource\Pages;

use Backstage\Resources\TypeResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateType extends CreateRecord
{
    protected static string $resource = TypeResource::class;

    protected function afterCreate(): void
    {
        $this->record->sites()->attach(Filament::getTenant());
    }
}
