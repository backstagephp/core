<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select as Input;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Models\Type;

class Select extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'searchable' => false,
            'multiple' => false,
            'preload' => false,
            'allowHtml' => false,
            'selectablePlaceholder' => false,
            'prefix' => null,
            'prefixIcon' => null,
            'prefixIconColor' => null,
            'suffix' => null,
            'suffixIcon' => null,
            'suffixIconColor' => null,
            'optionType' => null,
            'options' => [],
            'relations' => [],
            'contentType' => null,
            'relationKey' => null,
            'relationValue' => null,
            'loadingMessage' => null,
            'noSearchResultsMessage' => null,
            'searchPrompt' => null,
            'searchingMessage' => null,
            'searchDebounce' => null,
            'optionsLimit' => null,
            'minItemsForSearch' => null,
            'maxItemsForSearch' => null,
        ];
    }

    public static function make(string $name, Field $field): Input
    {
        $input = Input::make($name)
            ->label($field->name)
            ->required($field->config['required'] ?? self::getDefaultConfig()['required'])
            ->options($field->config['options'] ?? self::getDefaultConfig()['options'])
            ->searchable($field->config['searchable'] ?? self::getDefaultConfig()['searchable'])
            ->multiple($field->config['multiple'] ?? self::getDefaultConfig()['multiple'])
            ->preload($field->config['preload'] ?? self::getDefaultConfig()['preload'])
            ->allowHtml($field->config['allowHtml'] ?? self::getDefaultConfig()['allowHtml'])
            ->selectablePlaceholder($field->config['selectablePlaceholder'] ?? self::getDefaultConfig()['selectablePlaceholder'])
            ->prefix($field->config['prefix'] ?? self::getDefaultConfig()['prefix'])
            ->prefixIcon($field->config['prefixIcon'] ?? self::getDefaultConfig()['prefixIcon'])
            ->prefixIconColor($field->config['prefixIconColor'] ?? self::getDefaultConfig()['prefixIconColor'])
            ->suffix($field->config['suffix'] ?? self::getDefaultConfig()['suffix'])
            ->suffixIcon($field->config['suffixIcon'] ?? self::getDefaultConfig()['suffixIcon'])
            ->suffixIconColor($field->config['suffixIconColor'] ?? self::getDefaultConfig()['suffixIconColor'])
            ->loadingMessage($field->config['loadingMessage'] ?? self::getDefaultConfig()['loadingMessage'])
            ->noSearchResultsMessage($field->config['noSearchResultsMessage'] ?? self::getDefaultConfig()['noSearchResultsMessage'])
            ->searchPrompt($field->config['searchPrompt'] ?? self::getDefaultConfig()['searchPrompt'])
            ->searchingMessage($field->config['searchingMessage'] ?? self::getDefaultConfig()['searchingMessage']);

        if (isset($field->config['searchDebounce'])) {
            $input->searchDebounce($field->config['searchDebounce']);
        }

        if (isset($field->config['optionsLimit'])) {
            $input->optionsLimit($field->config['optionsLimit']);
        }

        if (isset($field->config['minItemsForSearch'])) {
            $input->minItemsForSearch($field->config['minItemsForSearch']);
        }

        if (isset($field->config['maxItemsForSearch'])) {
            $input->maxItemsForSearch($field->config['maxItemsForSearch']);
        }

        if ($field->config['optionType'] === 'relationship') {
            $options = [];

            foreach ($field->config['relations'] as $relation) {
                $type = Type::where('slug', $relation['contentType'])->first();

                if (! $type || ! $type->slug) {
                    continue;
                }

                $options[$type->slug] = $type->fields->pluck($relation['relationValue'], 'slug')->toArray();
            }

            $input->options($options);
        }

        if ($field->config['optionType'] === 'array') {
            $input->options($field->config['options']);
        }

        return $input;
    }

    public function getForm(): array
    {
        return [
            ...parent::getForm(),
            Forms\Components\Toggle::make('config.searchable')
                ->label(__('Searchable'))
                ->live(debounce: 250)
                ->inline(false),
            Forms\Components\Toggle::make('config.multiple')
                ->label(__('Multiple'))
                ->inline(false),
            Forms\Components\Toggle::make('config.allowHtml')
                ->label(__('Allow HTML'))
                ->inline(false),
            Forms\Components\Toggle::make('config.selectablePlaceholder')
                ->label(__('Selectable placeholder'))
                ->inline(false),
            Forms\Components\Toggle::make('config.preload')
                ->label(__('Preload'))
                ->live()
                ->inline(false)
                ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
            Forms\Components\Fieldset::make('Options')
                ->columnSpanFull()
                ->label(__('Options'))
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('config.optionType')
                                ->options([
                                    'array' => __('Array'),
                                    'relationship' => __('Relationship'),
                                ])
                                ->searchable()
                                ->live(onBlur: true)
                                ->reactive()
                                ->label(__('Type')),
                            // Array options
                            Forms\Components\KeyValue::make('config.options')
                                ->label(__('Options'))
                                ->columnSpanFull()
                                ->visible(fn(Forms\Get $get): bool => $get('config.optionType') == 'array')
                                ->required(fn(Forms\Get $get): bool => $get('config.optionType') == 'array'),
                            // Relationship options
                            Repeater::make('config.relations')
                                ->label(__('Relations'))
                                ->schema([
                                    Grid::make()
                                        ->columns(2)
                                        ->schema([
                                            Forms\Components\Select::make('contentType')
                                                ->label(__('Type'))
                                                ->searchable()
                                                ->preload()
                                                ->live(debounce: 250)
                                                ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                                    $type = Type::where('slug', $state)->first();

                                                    if (! $type || ! $type->slug) {
                                                        return;
                                                    }

                                                    $set('relationValue', $type->title_field ?? null);
                                                })
                                                ->options(fn() => Type::all()->pluck('name', 'slug'))
                                                ->noSearchResultsMessage(__('No types found'))
                                                ->required(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                                            Forms\Components\Hidden::make('relationKey')
                                                ->default('ulid')
                                                ->label(__('Key'))
                                                ->required(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                                            Forms\Components\Select::make('relationValue')
                                                ->options([
                                                    'slug' => __('Slug'),
                                                    'name' => __('Name'),
                                                ])
                                                ->disabled(fn(Forms\Get $get): bool => ! $get('contentType'))
                                                ->label(__('Label'))
                                                ->required(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                                        ]),
                                ])
                                ->visible(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship')
                                ->columnSpanFull(),
                        ]),
                ]),
            Forms\Components\Fieldset::make('Affixes')
                ->columnSpanFull()
                ->label(__('Affixes'))
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('config.prefix')
                                ->label(__('Prefix')),
                            Forms\Components\TextInput::make('config.prefixIcon')
                                ->placeholder('heroicon-m-')
                                ->label(__('Prefix icon')),
                            Forms\Components\ColorPicker::make('config.prefixIconColor')
                                ->label(__('Prefix color')),
                            Forms\Components\TextInput::make('config.suffix')
                                ->label(__('Suffix')),
                            Forms\Components\TextInput::make('config.suffixIcon')
                                ->placeholder('heroicon-m-')
                                ->label(__('Suffix icon')),
                            Forms\Components\ColorPicker::make('config.suffixIconColor')
                                ->label(__('Suffix color')),
                        ]),
                ]),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('config.loadingMessage')
                        ->label(__('Loading message'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                    Forms\Components\TextInput::make('config.noSearchResultsMessage')
                        ->label(__('No search results message'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                    Forms\Components\TextInput::make('config.searchPrompt')
                        ->label(__('Search prompt'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                    Forms\Components\TextInput::make('config.searchingMessage')
                        ->label(__('Searching message'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                    Forms\Components\TextInput::make('config.searchDebounce')
                        ->numeric()
                        ->minValue(0)
                        ->step(100)
                        ->label(__('Search debounce'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                    Forms\Components\TextInput::make('config.optionsLimit')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Options limit'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                    Forms\Components\TextInput::make('config.minItemsForSearch')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Min items for search'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                    Forms\Components\TextInput::make('config.maxItemsForSearch')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Max items for search'))
                        ->visible(fn(Forms\Get $get): bool => $get('config.searchable')),
                ]),
        ];
    }
}
