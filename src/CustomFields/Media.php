<?php

namespace Backstage\CustomFields;

use Backstage\Contracts\FieldContract;
use Backstage\Media\Fields\Media as MediaInput;
use Backstage\Models\Field;
use Backstage\Models\Media as MediaModel;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;

class Media extends FieldBase implements FieldContract
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'acceptedFileTypes' => ['image/*', 'video/*', 'audio/*', 'application/pdf'],
            'multiple' => false,
        ];
    }

    public static function make(string $name, Field $field): MediaInput
    {
        $input = self::applyDefaultSettings(MediaInput::make($name, $field), $field);

        if (! empty($field->config['acceptedFileTypes']) && ! is_array($field->config['acceptedFileTypes'])) {
            $field->config['acceptedFileTypes'] = [$field->config['acceptedFileTypes']];
        }

        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->acceptedFileTypes($field->config['acceptedFileTypes'] ?? self::getDefaultConfig()['acceptedFileTypes'])
            ->multiple($field->config['multiple'] ?? self::getDefaultConfig()['multiple']);

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
                                Forms\Components\Select::make('config.acceptedFileTypes')
                                    ->label(__('Accepted file types'))
                                    ->options([
                                        'image/*' => 'Images',
                                        'video/*' => 'Videos',
                                        'audio/*' => 'Audio',
                                        'application/pdf' => 'PDF',
                                    ])
                                    ->multiple(),
                                Forms\Components\Toggle::make('config.multiple')
                                    ->label(__('Multiple')),
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
        if ($field->field_type !== 'media') {
            return $data;
        }

        if (! isset($data['setting'][$field->slug])) {
            return $data;
        }

        $media = MediaInput::make($data['setting'][$field->slug], $field);

        $data['setting'][$field->slug] = collect($media)->map(function ($media) {
            return $media->ulid;
        })->toArray();

        return $data;
    }
}
