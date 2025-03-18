<?php

namespace Backstage\Resources\FormSubmissionValueResource\Pages;

use Backstage\Resources\FormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormSubmissionValue extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
