<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\Repeater as Input;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Vormkracht10\Backstage\Backstage;
use Vormkracht10\Backstage\Concerns\HasConfigurableFields;
use Vormkracht10\Backstage\Concerns\HasOptions;
use Vormkracht10\Backstage\Contracts\FieldContract;
use Vormkracht10\Backstage\Enums\Field as EnumsField;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Field;

class Repeater extends FieldBase implements FieldContract
{
    use HasConfigurableFields;
    use HasOptions;

    private const FIELD_TYPE_MAP = [
        'text' => TextInput::class,
        'textarea' => Textarea::class,
        'select' => Select::class,
    ];

    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'addActionLabel' => __('Add row'),
            'addable' => true,
            'deletable' => true,
            'reorderable' => false,
            'reorderableWithButtons' => false,
            'collapsible' => false,
            'collapsed' => false,
            'cloneable' => false,
        ];
    }

    public static function make(string $name, ?Field $field = null): Input
    {
        $input = self::applyDefaultSettings(Input::make($name), $field);

        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->addActionLabel($field->config['addActionLabel'] ?? self::getDefaultConfig()['addActionLabel'])
            ->addable($field->config['addable'] ?? self::getDefaultConfig()['addable'])
            ->deletable($field->config['deletable'] ?? self::getDefaultConfig()['deletable'])
            ->reorderable($field->config['reorderable'] ?? self::getDefaultConfig()['reorderable']);

        if ($field->config['reorderableWithButtons'] ?? self::getDefaultConfig()['reorderableWithButtons']) {
            $input = $input->reorderableWithButtons();
        }

        if ($field->config['form'] ?? false) {
            $input = $input->schema(
                self::buildFormSchema($field->config['form'])
            );
        }

        return $input;
    }

    private static function buildFormSchema(array $formFields): array
    {
        $schema = [];

        foreach ($formFields as $formField) {
            $fieldType = $formField['field_type'];

            if (! isset(self::FIELD_TYPE_MAP[$fieldType])) {
                continue;
            }

            $fieldClass = self::FIELD_TYPE_MAP[$fieldType];
            $field = $fieldClass::make($formField['name'])
                ->label($formField['label']);

            // Handle select field options
            if ($fieldType === 'select' && isset($formField['config'])) {
                if ($formField['config']['optionType'] === 'array' && ! empty($formField['config']['options'])) {
                    $field->options($formField['config']['options']);
                } elseif ($formField['config']['optionType'] === 'relationship' && ! empty($formField['config']['relations'])) {
                    $options = [];

                    foreach ($formField['config']['relations'] as $relation) {
                        $content = Content::where('type_slug', $relation['contentType'])->get();

                        if (! $content) {
                            continue;
                        }

                        $opts = $content->pluck($relation['relationValue'], 'ulid')->toArray();

                        if (count($opts) === 0) {
                            continue;
                        }

                        $options[] = $opts;
                    }

                    if (! empty($options)) {
                        $field->options(array_merge(...$options));
                    }
                }
            }

            $schema[] = $field;
        }

        return $schema;
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
                            Forms\Components\Toggle::make('config.addable')
                                ->label(__('Addable'))
                                ->inline(false),
                            Forms\Components\Toggle::make('config.deletable')
                                ->label(__('Deletable'))
                                ->inline(false),
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Toggle::make('config.reorderable')
                                    ->label(__('Reorderable'))
                                    ->live()
                                    ->inline(false),
                                Forms\Components\Toggle::make('config.reorderableWithButtons')
                                    ->label(__('Reorderable with buttons'))
                                    ->dehydrated()
                                    ->disabled(fn (Forms\Get $get): bool => $get('config.reorderable') === false)
                                    ->inline(false),
                            ]),
                            Forms\Components\Toggle::make('config.collapsible')
                                ->label(__('Collapsible'))
                                ->inline(false),
                            Forms\Components\Toggle::make('config.collapsed')
                                ->label(__('Collapsed'))
                                ->visible(fn (Forms\Get $get): bool => $get('config.collapsible') === true)
                                ->inline(false),
                            Forms\Components\Toggle::make('config.cloneable')
                                ->label(__('Cloneable'))
                                ->inline(false),
                            Forms\Components\TextInput::make('config.addActionLabel')
                                ->label(__('Add action label')),
                            Forms\Components\Fieldset::make('form')
                                ->label(__('Form'))
                                ->schema([
                                    Input::make('config.form')
                                        ->label(__('Fields'))
                                        ->addable()
                                        ->deletable()
                                        ->schema([
                                            Forms\Components\TextInput::make('name')
                                                ->label(__('Name'))
                                                ->required(),
                                            Forms\Components\TextInput::make('label')
                                                ->label(__('Label'))
                                                ->required(),
                                            Forms\Components\Select::make('field_type')
                                                ->searchable()
                                                ->preload()
                                                ->label(__('Field Type'))
                                                ->live(debounce: 250)
                                                ->reactive()
                                                ->options(
                                                    function () {
                                                        $options = array_merge(
                                                            collect(EnumsField::array())
                                                                ->filter(function ($value, $key) {
                                                                    return in_array($key, ['text', 'textarea', 'select']);
                                                                })
                                                                ->toArray(),
                                                            $this->formatCustomFields(Backstage::getFields())
                                                        );

                                                        asort($options);

                                                        return $options;
                                                    }
                                                )
                                                ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                                    if ($state === 'select') {
                                                        $set('config', [
                                                            'optionType' => null,
                                                            'options' => null,
                                                            'relations' => [],
                                                        ]);
                                                    } else {
                                                        $set('config', null);
                                                    }
                                                })
                                                ->required(),
                                            // Add options configuration for select fields
                                            Forms\Components\Select::make('config.optionType')
                                                ->options([
                                                    'array' => __('Array'),
                                                    'relationship' => __('Relationship'),
                                                ])
                                                ->searchable()
                                                ->live()
                                                ->visible(fn (Forms\Get $get): bool => $get('field_type') === 'select'),
                                            Forms\Components\KeyValue::make('config.options')
                                                ->label(__('Options'))
                                                ->visible(
                                                    fn (Forms\Get $get): bool => $get('field_type') === 'select' &&
                                                        $get('config.optionType') === 'array'
                                                ),
                                            Repeater::make('config.relations')
                                                ->label(__('Relations'))
                                                ->schema([
                                                    Forms\Components\Grid::make()
                                                        ->columns(2)
                                                        ->schema([
                                                            Forms\Components\Select::make('contentType')
                                                                ->label(__('Type'))
                                                                ->searchable()
                                                                ->preload()
                                                                ->options(fn () => Type::all()->pluck('name', 'slug')),
                                                            Forms\Components\Select::make('relationValue')
                                                                ->options([
                                                                    'slug' => __('Slug'),
                                                                    'name' => __('Name'),
                                                                ])
                                                                ->label(__('Label')),
                                                        ]),
                                                ])
                                                ->visible(
                                                    fn (Forms\Get $get): bool => $get('field_type') === 'select' &&
                                                        $get('config.optionType') === 'relationship'
                                                ),
                                        ])->columns(3),
                                ])->columns(1),
                        ])->columns(2),
                ])->columnSpanFull(),
        ];
    }
}
