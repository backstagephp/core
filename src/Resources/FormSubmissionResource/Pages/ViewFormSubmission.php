<?php

namespace Backstage\Resources\FormSubmissionResource\Pages;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use Backstage\Resources\FormSubmissionResource;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function getRecord(): Model
    {
        return parent::getRecord()->load(['values.field']);
    }
}
