<?php

namespace Vormkracht10\Backstage\Resources\FormSubmissionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Vormkracht10\Backstage\Resources\FormResource;

class ListFormSubmissions extends ListRecords
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
