<?php

namespace Backstage\Resources\FormActionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Backstage\Resources\FormResource;

class EditFormAction extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
