<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Enums\Field as EnumsField;
use Vormkracht10\Backstage\Models\Field;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    protected function getFieldTypeFormSchema(?string $fieldType): array
    {
        if (empty($fieldType)) {
            return [];
        }

        $className = 'Vormkracht10\\Backstage\\Fields\\' . Str::studly($fieldType);

        if (! class_exists($className) || ! method_exists($className, 'getForm')) {
            return [];
        }

        return app($className)->getForm();
    }

    public function form(Form $form): Form
    {
        $record = $form->getRecord();
        $fieldType = $record?->field_type ?? $form->getState()['field_type'] ?? null;

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
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->label(__('Field Type'))
                                    ->live()
                                    ->options(EnumsField::array())
                                    ->afterStateUpdated(function ($state, Set $set, Form $form) {
                                        if ($state !== $form->getRecord()?->field_type) {
                                            $set('config', []);
                                        }
                                    })
                                    ->required(),
                            ]),
                        Section::make('Rules')
                            ->columns(3)
                            ->schema([
                                // Dynamic field type specific form
                                ...($this->getFieldTypeFormSchema($fieldType)),
                            ]),
                    ]),
            ]);
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
                        dd('abc');
                        return [
                            ...$data,
                            'position' => Field::where('model_slug', $this->ownerRecord->id)->get()->max('position') + 1,
                            'model_type' => 'setting',
                            'model_slug' => $this->ownerRecord->slug,
                        ];
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->mutateRecordDataUsing(function (array $data) {
                        return [
                            ...$data,
                            'model_type' => 'setting',
                            'model_slug' => $this->ownerRecord->slug,
                        ];
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
}