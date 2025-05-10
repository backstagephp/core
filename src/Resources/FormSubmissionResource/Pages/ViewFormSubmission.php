<?php

namespace Backstage\Resources\FormSubmissionResource\Pages;

use Backstage\Resources\FormSubmissionResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function getRecord(): Model
    {
        return parent::getRecord()->load(['values.field']);
    }
}
