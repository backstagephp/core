<?php

namespace Backstage\Resources\FormSubmissionValueResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Backstage\Resources\FormResource;

class CreateFormAction extends CreateRecord
{
    protected static string $resource = FormResource::class;
}
