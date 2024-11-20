<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Locale;
use Vormkracht10\Backstage\Models\Domain;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Resources\DomainResource\Pages;

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
                                    ->afterStateUpdated(fn (string $state): string => preg_replace('/^(http)(s)?:\/\//i', '', $state))
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
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    Select::make('language_code')
                                        ->label(__('Language'))
                                        ->columnSpan(6)
                                        ->placeholder(__('Select Language'))
                                        ->prefixIcon('heroicon-o-language')
                                        ->options(
                                            Language::whereActive(1)->get()->mapWithKeys(fn ($language) => [
                                                $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage($language->code, app()->getLocale()),
                                            ])->sort()
                                        )
                                        ->allowHtml()
                                        ->default(Language::whereActive(1)->count() === 1 ? Language::whereActive(1)->first()->code : Language::whereActive(1)->where('default', true)->first()?->code)
                                        ->required(),
                                    Select::make('country_code')
                                        ->label(__('Country'))
                                        ->columnSpan(6)
                                        ->placeholder(fn () => Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 0 ? __('No countries available') : __('Select Country'))
                                        ->prefixIcon('heroicon-o-globe-europe-africa')
                                        ->options(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->get()->mapWithKeys(fn ($language) => [
                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage($language->code, app()->getLocale()),
                                        ])->sort())
                                        ->allowHtml()
                                        ->disabled(fn () => Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 0)
                                        ->default(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 1 ? Language::whereActive(1)->whereNotNull('country_code')->first()->country_code : null),
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
                TextColumn::make('environment')
                    ->badge()
                    ->color(fn (Domain $domain) => match ($domain->environment) {
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
