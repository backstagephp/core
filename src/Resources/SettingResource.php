<?php

namespace Backstage\Resources;

use Backstage\Fields\Filament\RelationManagers\FieldsRelationManager;
use Backstage\Translations\Laravel\Models\Language;
use Backstage\Models\Setting;
use Backstage\Models\Site;
use Backstage\Resources\SettingResource\Pages;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
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
use Locale;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Setup');
    }

    public static function getModelLabel(): string
    {
        return __('Setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Settings');
    }

    public static function fields(): array
    {
        return [
            TextInput::make('name')
                ->label(__('Name'))
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    $set('slug', Str::slug($state));
                }),
            TextInput::make('slug')
                ->label(__('Slug'))
                ->required()
                ->unique(ignoreRecord: true),

            Select::make('site_ulid')
                ->label(__('Site'))
                ->columnSpanFull()
                ->placeholder(__('Select Site'))
                ->prefixIcon('heroicon-o-link')
                ->options(Site::orderBy('default', 'desc')->orderBy('name', 'asc')->pluck('name', 'ulid'))
                ->default(Site::where('default', true)->first()?->ulid),

            Select::make('language_code')
                ->label(__('Language'))
                ->columnSpanFull()
                ->placeholder(__('Select Language'))
                ->prefixIcon('heroicon-o-language')
                ->options(
                    Language::where('active', 1)
                        ->get()
                        ->sort()
                        ->groupBy(function ($language) {
                            return Str::contains($language->code, '-') ? Locale::getDisplayRegion('-' . strtolower(explode('-', $language->code)[1]), app()->getLocale()) : 'Worldwide';
                        })
                        ->mapWithKeys(fn ($languages, $countryName) => [
                            $countryName => $languages->mapWithKeys(fn ($language) => [
                                $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' . $countryName . ')',
                            ])->toArray(),
                        ])
                )
                ->allowHtml()
                ->visible(fn () => Language::where('active', 1)->count() > 1),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Setting')
                            ->label(__('Setting'))
                            ->schema([
                                Grid::make()
                                    ->columns(2)
                                    ->schema(static::fields()),
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
                TextColumn::make('site.name')
                    ->label(__('Site'))
                    ->default('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('language_code')
                    ->label(__('Language'))
                    ->default('-')
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
