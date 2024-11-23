<?php

namespace Vormkracht10\Backstage\Resources\BlockResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Vormkracht10\Backstage\Resources\BlockResource;

class EditBlock extends EditRecord
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
