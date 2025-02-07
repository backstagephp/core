<?php

namespace Backstage\Resources\DomainResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Backstage\Resources\DomainResource;

class CreateDomain extends CreateRecord
{
    protected static string $resource = DomainResource::class;
}
