<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Vormkracht10\Backstage\Models\Domain;
use Vormkracht10\Backstage\Resources\DomainResource\Pages;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Setup');
    }

    public static function getModelLabel(): string
    {
        return __('Domain');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Domains');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Domain')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required(),
                                Select::make('language_code')
                                    ->relationship(name: 'language', titleAttribute: 'name')
                                    ->createOptionForm([
                                        TextInput::make('code')
                                            ->required(),
                                        TextInput::make('country_code')
                                            ->required(),
                                        TextInput::make('name')
                                            ->required(),
                                    ])
                                    ->searchable(['code', 'name', 'native'])
                                    ->columnSpanFull(),
                                TextInput::make('country_code')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDomains::route('/'),
            'create' => Pages\CreateDomain::route('/create'),
            'edit' => Pages\EditDomain::route('/{record}/edit'),
        ];
    }
}
