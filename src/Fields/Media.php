<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\MediaPicker\Components\MediaPicker;
use Vormkracht10\Backstage\Models\Media as MediaModel;

class Media extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
        ];
    }

    public static function make(string $name, Field $field): MediaPicker
    {
        $input = self::applyDefaultSettings(MediaPicker::make($name), $field);

        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null);

        return $input;
    }

    public function getForm(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->schema([
                    Forms\Components\Tabs\Tab::make('General')
                        ->label(__('General'))
                        ->schema([
                            ...parent::getForm(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Field specific')
                        ->label(__('Field specific'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                //
                            ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }

    public static function mutateFormDataCallback(Model $record, Field $field, array $data): array
    {
        if (! isset($record->values[$field->slug])) {
            return $data;
        }

        $media = MediaModel::whereIn('ulid', $record->values[$field->slug])
            ->get()
            ->map(function ($media) {
                return 'media/' . $media->filename;
            })->toArray();

        $data['setting'][$field->slug] = $media;

        return $data;
    }

    public static function mutateBeforeSaveCallback(Model $record, Field $field, array $data): array
    {
        $mediaFields = $record->fields->filter(function ($field) {
            return $field->field_type === 'media';
        });

        if ($mediaFields->count() === 0) {
            return $data;
        }

        foreach ($mediaFields as $field) {
            $media = MediaPicker::create($data['setting'][$field->slug]);

            $data['setting'][$field->slug] = collect($media)->map(function ($media) {
                return $media->ulid;
            })->toArray();
        }

        return $data;
    }
}