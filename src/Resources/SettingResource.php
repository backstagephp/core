<?php

namespace Vormkracht10\Backstage\Resources;

use Locale;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Vormkracht10\Backstage\Models\Site;
use Filament\Forms\Components\TextInput;
use Vormkracht10\Backstage\Models\Setting;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Resources\SettingResource\Pages;
use Vormkracht10\Backstage\Resources\SettingResource\RelationManagers\FieldsRelationManager;

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
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Name'))
                                            ->required()
                                            ->live(debounce: 250)
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
                                        Select::make('country_code')
                                            ->label(__('Country'))
                                            ->columnSpanFull()
                                            ->placeholder(__('Select Country'))
                                            ->prefixIcon('heroicon-o-globe-europe-africa')
                                            ->options(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->get()->mapWithKeys(fn ($language) => [
                                                $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage($language->code, app()->getLocale()),
                                            ])->sort())
                                            ->allowHtml()
                                            ->default(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 1 ? Language::whereActive(1)->whereNotNull('country_code')->first()->country_code : null),
                                        Select::make('language_code')
                                            ->label(__('Language'))
                                            ->columnSpanFull()
                                            ->placeholder(__('Select Language'))
                                            ->prefixIcon('heroicon-o-language')
                                            ->options(
                                                Language::whereActive(1)->get()->mapWithKeys(fn ($language) => [
                                                    $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage($language->code, app()->getLocale()),
                                                ])->sort()
                                            )
                                            ->allowHtml()
                                            ->default(Language::whereActive(1)->count() === 1 ? Language::whereActive(1)->first()->code : Language::whereActive(1)->where('default', true)->first()?->code),
                                    ]),
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
                TextColumn::make('country_code')
                    ->label(__('Country'))
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
