<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Resources\LanguageResource\Pages;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    public static ?string $recordTitleAttribute = 'name';

    protected static bool $isScopedToTenant = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Setup');
    }

    public static function getModelLabel(): string
    {
        return __('Language');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Languages');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->required()
                    ->columnSpan(1),
                TextInput::make('native')
                    ->required()
                    ->columnSpan(1),
                Toggle::make('active')
                    ->columnSpanFull(),
                Toggle::make('default')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('code')
                    ->label('')
                    ->width(20)
                    ->height(15)
                    ->getStateUsing(fn (Language $record) => 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $record->code . '.svg'))))
                    ->verticallyAlignCenter()
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('default')
                    ->label('Default')
                    ->width(0)
                    ->boolean(),
                IconColumn::make('active')
                    ->label('Active')
                    ->width(0)
                    ->boolean(),
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
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
