<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Facades\Filament;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Form as FormModel;
use Vormkracht10\Backstage\Resources\FormResource\Pages;
use Vormkracht10\Backstage\Resources\FormResource\RelationManagers\ActionsRelationManager;
use Vormkracht10\Backstage\Resources\FormResource\RelationManagers\FieldsRelationManager;

class FormResource extends Resource
{
    protected static ?string $model = FormModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'site';

    public static function getModelLabel(): string
    {
        return __('Form');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Forms');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Form')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->live(debounce: 250)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                TextInput::make('submit_button')
                                    ->label(__('Text on button'))
                                    ->placeholder(__('Send'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.backstage.resources.form-submissions.index', ['tenant' => Filament::getTenant(), 'tableFilters' => ['form_slug' => ['values' => [$record->slug]]]])),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FieldsRelationManager::class,
            ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit' => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
