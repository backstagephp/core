<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Resources\TypeResource\Pages;
use Vormkracht10\Fields\Filament\RelationManagers\FieldsRelationManager;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'sites';

    public static function getNavigationGroup(): ?string
    {
        return __('Structure');
    }

    public static function getModelLabel(): string
    {
        return __('Type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Types');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->required()
                            ->live(debounce: 250)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $set('slug', Str::slug($state));
                                $set('name_plural', Str::plural($state));
                            }),
                        TextInput::make('slug')
                            ->columnSpanFull()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if (in_array(strtolower($value), ['content', 'advanced'])) {
                                        $fail(__('This :attribute cannot be used.', ['attribute' => 'slug']));
                                    }
                                },
                            ]),
                        TextInput::make('name_plural')
                            ->columnSpanFull()
                            ->required(),
                        ToggleButtons::make('icon')
                            ->columnSpanFull()
                            ->default('circle-stack')
                            ->options([
                                'circle-stack' => '',
                                'light-bulb' => '',
                            ])
                            ->icons([
                                'circle-stack' => 'heroicon-o-circle-stack',
                                'light-bulb' => 'heroicon-o-light-bulb',
                            ])
                            ->inline()
                            ->grouped()
                            ->required(),

                        Select::make('template_slug')
                            ->columnSpanFull()
                            ->relationship(
                                'template',
                                'name',
                                fn ($query) => $query->whereHas('sites')
                            )
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('icon')
                    ->label('')
                    ->width(0)
                    ->icon(fn (string $state): string => 'heroicon-o-' . $state),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypes::route('/'),
            'create' => Pages\CreateType::route('/create'),
            'edit' => Pages\EditType::route('/{record}/edit'),
        ];
    }
}
