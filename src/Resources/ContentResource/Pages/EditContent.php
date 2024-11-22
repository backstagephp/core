<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Vormkracht10\Backstage\Resources\ContentResource;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['fields'] = $this->getRecord()->fields()->get()->mapWithKeys(function ($field) {
            return [$field->ulid => $field->pivot];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->getRecord()->fields()->sync($this->data['fields'] ?? []);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['fields']);

        return $data;
    }
}
