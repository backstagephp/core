<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Facades\Backstage;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Resources\BlockResource\Pages;
use Vormkracht10\Fields\Filament\RelationManagers\FieldsRelationManager;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'sites';

    public static function getNavigationGroup(): ?string
    {
        return __('Structure');
    }

    public static function getModelLabel(): string
    {
        return __('Block');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Blocks');
    }

    public static function getSlug(): string
    {
        return 'block';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Block')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', Str::slug($state));
                                    }),
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
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),
                        Tab::make('Component')
                            ->schema([
                                Select::make('component')
                                    ->columnSpanFull()
                                    ->options(Backstage::getComponentOptions()),
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
            'index' => Pages\ListBlocks::route('/'),
            'create' => Pages\CreateBlock::route('/create'),
            'edit' => Pages\EditBlock::route('/{record}/edit'),
        ];
    }
}
