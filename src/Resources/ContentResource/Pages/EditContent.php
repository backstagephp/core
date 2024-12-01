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
        $data['values'] = $this->getRecord()->values()->get()->mapWithKeys(function ($value) {
            if ($value->field->field_type == 'builder') {
                $value->value = json_decode($value->value, true);
            }

            return [$value->field->ulid => $value->value];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        collect($this->data['values'] ?? [])->each(function ($value, $field) {
            $value = isset($value['value']) && is_array($value['value']) ? json_encode($value['value']) : $value;

            if (blank($value)) {
                $this->getRecord()->values()->where([
                    'content_ulid' => $this->getRecord()->getKey(),
                    'field_ulid' => $field,
                ])->delete();

                return;
            }

            $this->getRecord()->values()->updateOrCreate([
                'content_ulid' => $this->getRecord()->getKey(),
                'field_ulid' => $field,
            ], [
                'value' => is_array($value) ? json_encode($value) : $value,
            ]);
        });
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['values']);

        return $data;
    }
}
