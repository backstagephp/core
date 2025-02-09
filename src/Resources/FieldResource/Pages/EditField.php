<?php

namespace Backstage\Resources\FieldResource\Pages;

use Backstage\Resources\FieldResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditField extends EditRecord
{
    protected static string $resource = FieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
