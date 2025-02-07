<?php

namespace Backstage\Resources\FieldResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Backstage\Resources\FieldResource;

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
