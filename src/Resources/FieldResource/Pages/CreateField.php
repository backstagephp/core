<?php

namespace Vormkracht10\Backstage\Resources\FieldResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Vormkracht10\Backstage\Resources\LanguageResource;

class CreateField extends CreateRecord
{
    protected static string $resource = LanguageResource::class;
}
