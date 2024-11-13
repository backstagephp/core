<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Resources\SiteResource\Pages;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-window';

    public static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Setup');
    }

    public static function getModelLabel(): string
    {
        return __('Site');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Sites');
    }

    public static function form(Form $form): Form
    {
        return $form
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
                    ->required(),
                TextInput::make('title')
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('title_separator')
                    ->columnSpanFull()
                    ->default(',')
                    ->required(),
                TextInput::make('path')
                    ->columnSpanFull()
                    ->default('/')
                    ->required(),
                TextInput::make('timezone')
                    ->columnSpanFull()
                    ->default('UTC')
                    ->required(),
                Checkbox::make('auth'),
                Checkbox::make('default'),
                Checkbox::make('trailing_slash'),
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
