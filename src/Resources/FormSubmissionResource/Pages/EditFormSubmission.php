<?php

namespace Vormkracht10\Backstage\Resources\FormSubmissionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Vormkracht10\Backstage\Resources\FormResource;

class EditFormSubmission extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
