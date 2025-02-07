<?php

namespace Backstage\Resources\FormSubmissionResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Backstage\Resources\FormSubmissionResource;

class ListFormSubmissions extends ListRecords
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
