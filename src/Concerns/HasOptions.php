<?php

namespace Vormkracht10\Backstage\Concerns;

use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Type;

trait HasOptions
{
    public static function addOptionsToInput(mixed $input, mixed $field): mixed
    {
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

    public static function getOptionsConfig(): array
    {
        return [
            'optionType' => null,
            'options' => [],
            'descriptions' => [],
            'relations' => [],
            'contentType' => null,
            'relationKey' => null,
            'relationValue' => null,
        ];
    }

    public function optionFormFields(): Fieldset
    {
        return Forms\Components\Fieldset::make('Options')
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

                                                $set('relationValue', $type->name_field ?? null);
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
            ]);
    }
}
