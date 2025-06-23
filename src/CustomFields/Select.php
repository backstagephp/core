<?php

namespace Backstage\CustomFields;

use Backstage\Fields\Fields\Select as Base;
use Backstage\Fields\Models\Field;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Forms;

class Select extends Base
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'filterByLanguage' => false,
        ];
    }

    public static function mutateFormDataCallback(Model $record, Field $field, array $data): array
    {
        return $data;
    }

    public static function mutateBeforeSaveCallback(Model $record, Field $field, array $data): array
    {
        if (! isset($field->config['optionType']) || $field->config['optionType'] !== 'relationship' || empty($field->config['relations'])) {
            return $data;
        }

        DB::table('relationables')
            ->where('relation_type', $record->getMorphClass())
            ->where('relation_ulid', $record->ulid)
            ->delete();

        $values = $data['values'][$field->ulid];
        if (! is_array($values)) {
            $values = [$values];
        }

        foreach ($field->config['relations'] as $relation) {
            $resource = $relation['resource'];
            $key = $relation['relationKey'];

            /** @phpstan-ignore-next-line */
            $instance = new static;
            $model = $instance->resolveResourceModel($resource);

            $query = $model->whereIn($key, $values);

            // Filter by same language code if the option is enabled and record has language_code
            if (isset($field->config['filterByLanguage']) && $field->config['filterByLanguage'] && $record && isset($record->language_code)) {
                $query->where('language_code', $record->language_code);
            }

            $results = $query->get();

            foreach ($results as $result) {
                DB::table('relationables')->insert([
                    'relation_type' => $record->getMorphClass(),
                    'relation_ulid' => $record->ulid,
                    'related_type' => $resource,
                    'related_ulid' => $result->ulid,
                ]);
            }
        }

        return $data;
    }

    public static function addOptionsToInput(mixed $input, mixed $field, ?Model $record = null): mixed
    {
        if (isset($field->config['optionType']) && $field->config['optionType'] === 'relationship') {
            $options = [];

            foreach ($field->config['relations'] as $relation) {
                if (! isset($relation['resource'])) {
                    continue;
                }

                $model = static::resolveResourceModel($relation['resource']);

                if (! $model) {
                    continue;
                }

                $query = $model::query();

                // Apply filters if they exist
                if (isset($relation['relationValue_filters'])) {
                    foreach ($relation['relationValue_filters'] as $filter) {
                        if (isset($filter['column'], $filter['operator'], $filter['value'])) {
                            $query->where($filter['column'], $filter['operator'], $filter['value']);
                        }
                    }
                }

                // Filter by same language code if the option is enabled and record has language_code
                if (isset($field->config['filterByLanguage']) && $field->config['filterByLanguage'] && $record && isset($record->language_code)) {
                    $query->where('language_code', $record->language_code);
                }

                $results = $query->get();

                if ($results->isEmpty()) {
                    continue;
                }

                $opts = $results->pluck($relation['relationValue'] ?? 'name', $relation['relationKey'])->toArray();

                if (count($opts) === 0) {
                    continue;
                }

                $options[] = $opts;
            }

            if (! empty($options)) {
                $options = array_merge(...$options);
                $input->options($options);
            }
        }

        if (isset($field->config['optionType']) && $field->config['optionType'] === 'array') {
            $input->options($field->config['options']);
        }

        return $input;
    }

    public static function make(string $name, ?Field $field = null, ?Model $record = null): \Filament\Forms\Components\Select
    {
        // Debug: Check what record is passed to Select
        \Illuminate\Support\Facades\Log::info('Select::make called', [
            'name' => $name,
            'record' => $record,
            'record_class' => $record ? get_class($record) : null,
            'language_code' => $record?->language_code ?? null,
        ]);

        $input = self::applyDefaultSettings(\Filament\Forms\Components\Select::make($name), $field);

        $input = $input->label($field->name ?? null)
            ->searchable($field->config['searchable'] ?? self::getDefaultConfig()['searchable'])
            ->multiple($field->config['multiple'] ?? self::getDefaultConfig()['multiple'])
            ->preload($field->config['preload'] ?? self::getDefaultConfig()['preload'])
            ->allowHtml($field->config['allowHtml'] ?? self::getDefaultConfig()['allowHtml'])
            ->selectablePlaceholder($field->config['selectablePlaceholder'] ?? self::getDefaultConfig()['selectablePlaceholder'])
            ->loadingMessage($field->config['loadingMessage'] ?? self::getDefaultConfig()['loadingMessage'])
            ->noSearchResultsMessage($field->config['noSearchResultsMessage'] ?? self::getDefaultConfig()['noSearchResultsMessage'])
            ->searchPrompt($field->config['searchPrompt'] ?? self::getDefaultConfig()['searchPrompt'])
            ->searchingMessage($field->config['searchingMessage'] ?? self::getDefaultConfig()['searchingMessage']);

        $input = self::addAffixesToInput($input, $field);
        $input = self::addOptionsToInput($input, $field, $record);

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
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\Toggle::make('config.filterByLanguage')
                                        ->label(__('Filter by same language'))
                                        ->helperText(__('Only include relations with the same language code'))
                                        ->inline(false),
                                ])->columnSpanFull(),
                            self::optionFormFields(),
                            self::affixFormFields(),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('config.loadingMessage')
                                        ->label(__('Loading message'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.noSearchResultsMessage')
                                        ->label(__('No search results message'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.searchPrompt')
                                        ->label(__('Search prompt'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.searchingMessage')
                                        ->label(__('Searching message'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.searchDebounce')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step(100)
                                        ->label(__('Search debounce'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.optionsLimit')
                                        ->numeric()
                                        ->minValue(0)
                                        ->label(__('Options limit'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.minItemsForSearch')
                                        ->numeric()
                                        ->minValue(0)
                                        ->label(__('Min items for search'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                    Forms\Components\TextInput::make('config.maxItemsForSearch')
                                        ->numeric()
                                        ->minValue(0)
                                        ->label(__('Max items for search'))
                                        ->visible(fn (Forms\Get $get): bool => $get('config.searchable')),
                                ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}
