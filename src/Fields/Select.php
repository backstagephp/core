<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Vormkracht10\Backstage\Models\Tag;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Models\User;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Models\Media;
use Vormkracht10\Backstage\Models\Domain;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Language;
use Filament\Forms\Components\Select as Input;

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
            'relation' => null,
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

        if ($field->config['searchDebounce']) {
            $input->searchDebounce($field->config['searchDebounce']);
        }

        if ($field->config['optionsLimit']) {
            $input->optionsLimit($field->config['optionsLimit']);
        }

        if ($field->config['minItemsForSearch']) {
            $input->minItemsForSearch($field->config['minItemsForSearch']);
        }

        if ($field->config['maxItemsForSearch']) {
            $input->maxItemsForSearch($field->config['maxItemsForSearch']);
        }

        // if ($field->config['optionType'] === 'relationship') {
        //     $input->options(fn() => $field->config['relation']::all()->pluck($field->config['relationValue'], $field->config['relationKey']));
        // }

        // if ($field->config['optionType'] === 'array') {
        //     $input->options($field->config['options']);
        // }

        return $input;
    }

    public function getForm(): array
    {
        return [
            ...parent::getForm(),
            Forms\Components\Toggle::make('config.searchable')
                ->label(__('Searchable'))
                ->inline(false),
            Forms\Components\Toggle::make('config.multiple')
                ->label(__('Multiple'))
                ->inline(false),
            Forms\Components\Toggle::make('config.preload')
                ->label(__('Preload'))
                ->inline(false),
            Forms\Components\Toggle::make('config.allowHtml')
                ->label(__('Allow HTML'))
                ->inline(false),
            Forms\Components\Toggle::make('config.selectablePlaceholder')
                ->label(__('Selectable placeholder'))
                ->inline(false),
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
                                ->native(false)
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
                            Forms\Components\Select::make('config.relation')
                                ->label(__('Relation'))
                                ->options([
                                    Content::class => __('Content'),
                                    Domain::class => __('Domain'),
                                    Field::class => __('Field'),
                                    Language::class => __('Language'),
                                    Media::class => __('Media'),
                                    Site::class => __('Site'),
                                    Tag::class => __('Tag'),
                                    Type::class => __('Type'),
                                    User::class => __('User'),
                                ])
                                ->visible(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship')
                                ->required(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                            // TODO: Could be a select based on the chosen class, but for now we'll just use text inputs
                            Forms\Components\TextInput::make('config.relationKey')
                                ->label(__('Relation key'))
                                ->visible(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship')
                                ->required(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship'),
                            Forms\Components\TextInput::make('config.relationValue')
                                ->label(__('Relation value'))
                                ->visible(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship')
                                ->required(fn(Forms\Get $get): bool => $get('config.optionType') == 'relationship'),

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
                        ->label(__('Loading message')),
                    Forms\Components\TextInput::make('config.noSearchResultsMessage')
                        ->label(__('No search results message')),
                    Forms\Components\TextInput::make('config.searchPrompt')
                        ->label(__('Search prompt')),
                    Forms\Components\TextInput::make('config.searchingMessage')
                        ->label(__('Searching message')),
                    Forms\Components\TextInput::make('config.searchDebounce')
                        ->numeric()
                        ->minValue(0)
                        ->step(100)
                        ->label(__('Search debounce')),
                    Forms\Components\TextInput::make('config.optionsLimit')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Options limit')),
                    Forms\Components\TextInput::make('config.minItemsForSearch')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Min items for search')),
                    Forms\Components\TextInput::make('config.maxItemsForSearch')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Max items for search')),
                ]),
        ];
    }
}
