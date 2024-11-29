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
            $pivot = $field->pivot;
            if ($field->field_type == 'builder') {
                $pivot->value = json_decode($pivot->value, true);
            }

            return [$field->ulid => $pivot];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $fields = collect($this->data['fields'] ?? []);
        $fields = $fields->map(function ($field) {
            return isset($field['value']) && is_array($field['value']) ? ['value' => json_encode($field['value'])] : $field;
        });

        $this->getRecord()->fields()->sync($fields);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['fields']);

        return $data;
    }
}
