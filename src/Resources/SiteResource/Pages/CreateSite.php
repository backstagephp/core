<?php

namespace Vormkracht10\Backstage\Resources\SiteResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Vormkracht10\Backstage\Resources\LanguageResource;

class CreateSite extends CreateRecord
{
    protected static string $resource = LanguageResource::class;
}
