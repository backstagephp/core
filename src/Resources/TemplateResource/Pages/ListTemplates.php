<?php

namespace Vormkracht10\Backstage\Resources\TemplateResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Resources\TemplateResource;

class ListTemplates extends ListRecords
{
    protected static string $resource = TemplateResource::class;
}
