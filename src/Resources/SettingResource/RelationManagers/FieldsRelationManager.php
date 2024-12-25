<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\RelationManagers;

use Exception;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Facades\Backstage;
use Vormkracht10\Backstage\Enums\Field as EnumsField;
use Filament\Resources\RelationManagers\RelationManager;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        Section::make('Field')
                            ->columns(3)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->placeholder(__('Name'))
                                    ->live(debounce: 250)
                                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
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

                                            return asort($options);
                                        }
                                    )
                                    ->required()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('config', []);

                                        $set('config', EnumsField::tryFrom($state)
                                            ? $this->initializeDefaultConfig($state)
                                            : $this->initializeCustomConfig($state));
                                    }),
                            ]),
                        Section::make('Configuration')
                            ->columns(3)
                            ->schema(fn(Get $get) => $this->getFieldTypeFormSchema(
                                $get('field_type')
                            ))
                            ->visible(fn(Get $get) => filled($get('field_type'))),
                    ]),
            ]);
    }

    private function formatCustomFields(array $fields): array
    {
        return collect($fields)->mapWithKeys(function ($field, $key) {
            $parts = explode('\\', $field);
            $lastPart = end($parts);
            $formattedName = ucwords(str_replace('_', ' ', strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $lastPart))));

            return [$key => $formattedName];
        })->toArray();
    }

    private function initializeDefaultConfig(string $fieldType): array
    {
        $className = 'Vormkracht10\\Backstage\\Fields\\' . Str::studly($fieldType);

        if (! class_exists($className)) {
            return [];
        }

        $fieldInstance = app($className);

        return $fieldInstance::getDefaultConfig();
    }

    private function initializeCustomConfig(string $fieldType): array
    {
        $className = Backstage::getFields()[$fieldType] ?? null;

        if (! class_exists($className)) {
            return [];
        }

        $fieldInstance = app($className);

        return $fieldInstance::getDefaultConfig();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('position')
            ->defaultSort('position', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->limit(),

                Tables\Columns\TextColumn::make('field_type')
                    ->label(__('Type'))
                    ->searchable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data) {
                        return [
                            ...$data,
                            'position' => Field::where('model_key', $this->ownerRecord->id)->get()->max('position') + 1,
                            'model_type' => 'setting',
                            'model_key' => $this->ownerRecord->slug,
                        ];
                    })
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshFields');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->mutateRecordDataUsing(function (array $data) {
                        return [
                            ...$data,
                            'model_type' => 'setting',
                            'model_key' => $this->ownerRecord->slug,
                        ];
                    })
                    ->mutateFormDataUsing(fn(array $data, Model $record): array => $this->handleUpdatingSlugs($data, $record))
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshFields');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function (Component $livewire, array $data, Model $record, array $arguments) {
                        $this->ownerRecord->update([
                            'values' => collect($this->ownerRecord->values)->forget($record->slug)->toArray(),
                        ]);
                        $livewire->dispatch('refreshFields');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshFields');
                        }),
                ]),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Fields');
    }

    public static function getModelLabel(): string
    {
        return __('Field');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Fields');
    }

    private function handleUpdatingSlugs(array $data, Model $record): array
    {
        $oldSlug = $record->slug;
        $newSlug = $data['slug'];

        if ($newSlug === $oldSlug) {
            return $data;
        }

        $existingValues = $this->ownerRecord->values;

        // Handle slug update in existing values
        if (isset($existingValues[$oldSlug])) {
            // Transfer value from old slug to new slug
            $existingValues[$newSlug] = $existingValues[$oldSlug];
            unset($existingValues[$oldSlug]);

            $this->ownerRecord->update([
                'values' => $existingValues,
            ]);
        } else {
            $existingValues[$newSlug] = null;
        }

        return $data;
    }
}