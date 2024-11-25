<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Models\Content;
use Filament\Forms\Components\CheckboxList as Input;

class CheckboxList extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'searchable' => false,
            'allowHtml' => false,
            'optionType' => null,
            'options' => [],
            'descriptions' => [],
            'relations' => [],
            'contentType' => null,
            'relationKey' => null,
            'relationValue' => null,
            'columns' => 1,
            'gridDirection' => 'row',
            'bulkToggleable' => false,
            'noSearchResultsMessage' => null,
            'searchPrompt' => null,
            'searchDebounce' => null,
        ];
    }

    public static function make(string $name, ?Field $field = null): Input
    {
        $input = self::applyDefaultSettings(Input::make($name), $field);

        $input = $input->label($field->name ?? null)
            ->searchable($field->config['searchable'] ?? self::getDefaultConfig()['searchable'])
            ->allowHtml($field->config['allowHtml'] ?? self::getDefaultConfig()['allowHtml'])
            ->options($field->config['options'] ?? self::getDefaultConfig()['options'])
            ->descriptions($field->config['descriptions'] ?? self::getDefaultConfig()['descriptions'])
            ->columns($field->config['columns'] ?? self::getDefaultConfig()['columns'])
            ->gridDirection($field->config['gridDirection'] ?? self::getDefaultConfig()['gridDirection'])
            ->bulkToggleable($field->config['bulkToggleable'] ?? self::getDefaultConfig()['bulkToggleable'])
            ->noSearchResultsMessage($field->config['noSearchResultsMessage'] ?? self::getDefaultConfig()['noSearchResultsMessage'])
            ->searchPrompt($field->config['searchPrompt'] ?? self::getDefaultConfig()['searchPrompt']);

        if (isset($field->config['searchDebounce'])) {
            $input->searchDebounce($field->config['searchDebounce']);
        }

        if (isset($field->config['optionType']) && $field->config['optionType'] === 'relationship') {
            $options = [];

            foreach ($field->config['relations'] as $relation) {
                $content = Content::where('type_slug', $relation['contentType'])->get();

                if (! $content) {
                    continue;
                }

                $opts = $content->pluck($relation['relationValue'], 'ulid')->toArray();

                if (count($opts) === 0) {
                    continue;
                }

                // CheckboxList cannot be grouped.
                $options[] = $opts;
            }

            $options = array_merge(...$options);

            $input->options($options);
        }

        if (isset($field->config['optionType']) && $field->config['optionType'] === 'array') {
            $input->options($field->config['options']);
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
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Toggle::make('config.searchable')
                                        ->label(__('Searchable'))
                                        ->live(debounce: 250)
                                        ->inline(false),
                                    Forms\Components\Toggle::make('config.allowHtml')
                                        ->label(__('Allow HTML'))
                                        ->inline(false),
                                    Forms\Components\Toggle::make('config.bulkToggleable')
                                        ->label(__('Bulk toggle'))
                                        ->inline(false),
                                ]),
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
                                                ->visible(fn (Forms\Get $get): bool => $get('config.optionType') == 'array')
                                                ->required(fn (Forms\Get $get): bool => $get('config.optionType') == 'array'),
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
                                                                ->options(fn () => Type::all()->pluck('name', 'slug'))
                                                                ->noSearchResultsMessage(__('No types found'))
                                                                ->required(fn (Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                                                            Forms\Components\Hidden::make('relationKey')
                                                                ->default('ulid')
                                                                ->label(__('Key'))
                                                                ->required(fn (Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                                                            Forms\Components\Select::make('relationValue')
                                                                ->options([
                                                                    'slug' => __('Slug'),
                                                                    'name' => __('Name'),
                                                                ])
                                                                ->disabled(fn (Forms\Get $get): bool => ! $get('contentType'))
                                                                ->label(__('Label'))
                                                                ->required(fn (Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                                                        ]),
                                                ])
                                                ->visible(fn (Forms\Get $get): bool => $get('config.optionType') == 'relationship')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('config.columns')
                                        ->numeric()
                                        ->minValue(1)
                                        ->label(__('Columns')),
                                    Forms\Components\Select::make('config.gridDirection')
                                        ->options([
                                            'row' => __('Row'),
                                            'column' => __('Column'),
                                        ])
                                        ->label(__('Grid direction')),
                                    //
                                    Forms\Components\TextInput::make('config.noSearchResultsMessage')
                                        ->label(__('No search results message'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.searchPrompt')
                                        ->label(__('Search prompt'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.searchDebounce')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step(100)
                                        ->label(__('Search debounce'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}
