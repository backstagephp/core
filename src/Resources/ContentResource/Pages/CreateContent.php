<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vormkracht10\Backstage\Resources\ContentResource;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected static ?string $slug = 'content/create/{type}';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->fields->count() === 0) {
            return $data;
        }

        foreach ($this->record->values as $slug => $value) {
            $data['meta'][$slug] = $value;
        }

        return $data;
    }
}
