<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Vormkracht10\Backstage\Models\Field;
use Filament\Tables\Actions\AttachAction;
use Vormkracht10\Backstage\Enums\Field as EnumsField;
use Filament\Resources\RelationManagers\RelationManager;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
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
                            ->options(EnumsField::array())
                            ->required(),
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['position'] = Field::where('model_slug', $this->ownerRecord->id)->get()->max('position') + 1;
                        $data['model_type'] = get_class($this->ownerRecord);
                        $data['model_slug'] = $this->ownerRecord->slug;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}