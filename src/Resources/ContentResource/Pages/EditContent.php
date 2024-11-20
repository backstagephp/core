<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Vormkracht10\Backstage\Resources\ContentResource;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->type?->fields->count() === 0) {
            return $data;
        }

        foreach ($this->record->values as $value) {
            $data['values'][] = $value;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
