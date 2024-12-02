<?php

namespace Vormkracht10\Backstage\Resources\TagResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vormkracht10\Backstage\Resources\TagResource;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['site_ulid'] = filament()->getTenant()->ulid;

        return $data;
    }
}
