<?php

namespace Backstage\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Backstage\Models\Domain;
use Backstage\Resources\DomainResource\Pages;
use Backstage\Resources\DomainResource\RelationManagers\LanguagesRelationManager;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationParentItem = 'Sites';

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
                                    ->label('Domain name')
                                    ->columnSpanFull()
                                    ->afterStateUpdated(fn(string $state): string => preg_replace('/^(http)(s)?:\/\//i', '', $state))
                                    ->required(),
                                Select::make('environment')
                                    ->label('Environment')
                                    ->columnSpanFull()
                                    ->options([
                                        'local' => __('Local'),
                                        'production' => __('Production'),
                                        'staging' => __('Staging'),
                                    ])
                                    ->required(),
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
                TextColumn::make('environment')
                    ->badge()
                    ->color(fn(Domain $domain) => match ($domain->environment) {
                        'production' => 'success',
                        'staging' => 'warning',
                        'local' => 'red',
                    })
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
            LanguagesRelationManager::class,
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
