<?php

namespace Backstage\Resources;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Backstage\Models\Type;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Backstage\Resources\TypeResource\Pages;
use Filament\Forms\Components\ToggleButtons;
use Backstage\Fields\Filament\RelationManagers\FieldsRelationManager;

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
                Grid::make('Type')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(debounce: 250)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Type $record) {
                                $set('name_plural', Str::plural($state));

                                $currentSlug = $get('slug');

                                if ($record && $record->slug) {
                                    return;
                                }

                                if (! $currentSlug || $currentSlug === Str::slug($old)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('name_plural')
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rules([
                                fn(): \Closure => function (string $attribute, $value, \Closure $fail) {
                                    if (in_array(strtolower($value), ['content', 'advanced', 'default'])) {
                                        $fail(__('This :attribute cannot be used.', ['attribute' => 'slug']));
                                    }
                                },
                            ]),
                        ToggleButtons::make('icon')
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
                        Toggle::make('public')
                            ->label('Public')
                            ->inline(false)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('icon')
                    ->label('')
                    ->width(1)
                    ->icon(fn(string $state): string => 'heroicon-o-' . $state),
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
