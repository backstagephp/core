<?php

namespace Vormkracht10\Backstage\Fields;

use Exception;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater as Input;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Saade\FilamentAdjacencyList\Forms\Components\AdjacencyList;
use Vormkracht10\Backstage\Backstage;
use Vormkracht10\Backstage\Concerns\HasConfigurableFields;
use Vormkracht10\Backstage\Concerns\HasOptions;
use Vormkracht10\Backstage\Contracts\FieldContract;
use Vormkracht10\Backstage\Enums\Field as EnumsField;
use Vormkracht10\Backstage\Fields\Select as FieldsSelect;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Models\Type;

class Repeater extends FieldBase implements FieldContract
{
    use HasConfigurableFields;
    use HasOptions;

    private const FIELD_TYPE_MAP = [
        'text' => Text::class,
        'textarea' => Textarea::class,
        'rich-editor' => RichEditor::class,
        'repeater' => Repeater::class,
        'select' => FieldsSelect::class,
        'checkbox' => Checkbox::class,
        'checkbox-list' => CheckboxList::class,
        'media' => Media::class,
        'key-value' => KeyValue::class,
        'radio' => Radio::class,
        'toggle' => Toggle::class,
        'color' => Color::class,
        'datetime' => DateTime::class,
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
            $schema = [];

            foreach ($field->children as $f) {
                $instance = new self;

                $schema[] = $instance->resolveFieldTypeClassName($f->field_type)::make($f->name, $f);
            }

            $input = $input->schema($schema);
        }

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
                                            AdjacencyList::make('field_type')
                                                ->columnSpanFull()
                                                ->label(__('Field Type'))
                                                ->relationship('children')
                                                ->live(debounce: 250)
                                                ->labelKey('name')
                                                ->maxDepth(1)
                                                ->form([
                                                    Section::make('Field')
                                                        ->columns(3)
                                                        ->schema([
                                                            Hidden::make('model_type')
                                                                ->default('field'),
                                                            Hidden::make('model_key')
                                                                ->default('slug'),
                                                            TextInput::make('name')
                                                                ->label(__('Name'))
                                                                ->required()
                                                                ->placeholder(__('Name'))
                                                                ->live(debounce: 250)
                                                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                                            TextInput::make('slug')
                                                                ->readonly(),
                                                            Select::make('field_type')
                                                                ->searchable()
                                                                ->preload()
                                                                ->label(__('Field Type'))
                                                                ->live(debounce: 250)
                                                                ->reactive()
                                                                ->options(
                                                                    function () {
                                                                        $options = array_merge(
                                                                            EnumsField::array(),
                                                                            $this->formatCustomFields(Backstage::getFields())
                                                                        );

                                                                        asort($options);

                                                                        return $options;
                                                                    }
                                                                )
                                                                ->required()
                                                                ->afterStateUpdated(function ($state, Set $set) {
                                                                    $set('config', []);

                                                                    $set('config', EnumsField::tryFrom($state)
                                                                        ? $this->initializeDefaultConfig($state)
                                                                        : $this->initializeCustomConfig($state));
                                                                }),
                                                        ])->columnSpanFull(),
                                                    Section::make('Configuration')
                                                        ->columns(3)
                                                        ->schema(fn (Get $get) => $this->getFieldTypeFormSchema(
                                                            $get('field_type')
                                                        ))
                                                        ->visible(fn (Get $get) => filled($get('field_type'))),
                                                ])
                                                ->required(),
                                        ])->columns(3),
                                ])->columns(1),
                        ])->columns(2),
                ])->columnSpanFull(),
        ];
    }

    /** @throws Exception If the field type class cannot be resolved. */
    protected function getFieldTypeFormSchema(?string $fieldType): array
    {
        if (empty($fieldType)) {
            return [];
        }

        try {
            $className = $this->resolveFieldTypeClassName($fieldType);

            if (! $this->isValidFieldClass($className)) {
                return [];
            }

            return app($className)->getForm();
        } catch (Exception $e) {
            throw new Exception("Failed to resolve field type class for '{$fieldType}'");
        }
    }

    protected function resolveFieldTypeClassName(string $fieldType): ?string
    {
        if (EnumsField::tryFrom($fieldType)) {
            return sprintf('Vormkracht10\\Backstage\\Fields\\%s', Str::studly($fieldType));
        }

        return Backstage::getFields()[$fieldType] ?? null;
    }

    protected function isValidFieldClass(?string $className): bool
    {
        return $className !== null
            && class_exists($className)
            && method_exists($className, 'getForm');
    }
}
