<?php

namespace Vormkracht10\Backstage\Resources\DomainResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Vormkracht10\Backstage\Resources\LanguageResource;

class CreateDomain extends CreateRecord
{
    protected static string $resource = LanguageResource::class;
}
