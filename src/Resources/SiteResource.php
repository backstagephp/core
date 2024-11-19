<?php

namespace Vormkracht10\Backstage\Resources;

use Locale;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Vormkracht10\Backstage\Models\Site;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Vormkracht10\Backstage\Models\Language;
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
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Site')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    TextInput::make('name')
                                        ->label('Name')
                                        ->columnSpan(6)
                                        ->helperText('The name of the site, used internally in the back-end only.')
                                        ->live(debounce: 250)
                                        ->afterStateUpdated(function (Set $set, ?string $state) {
                                            $set('slug', Str::slug($state));
                                            $set('name_plural', Str::plural($state));
                                        })
                                        ->required(),
                                    TextInput::make('slug')
                                        ->columnSpan(6)
                                        ->helperText('The slug for this site, used in code.')
                                        ->required(),
                                    TextInput::make('title')
                                        ->label('Title')
                                        ->columnSpan(6)
                                        ->helperText('The title of the site, used in the front-end for SEO.')
                                        ->required(),
                                    TextInput::make('title_separator')
                                        ->label('Title separator')
                                        ->columnSpan(6)
                                        ->default('|')
                                        ->helperText('Separator symbol in the page title. E.g. "Page Title | Name of site"')
                                        ->required(),
                                    Select::make('theme')
                                        ->label('Theme')
                                        ->columnSpanFull()
                                        ->helperText('Select default theme.')
                                        ->required(),
                                    Toggle::make('default')
                                        ->label('Use this site as default.')
                                        ->columnSpanFull()
                                        ->required(),
                                    Toggle::make('auth')
                                        ->label('Protect site behind authentication.')
                                        ->columnSpanFull()
                                        ->required(),
                                ]),
                            ]),
                        Tab::make('Branding')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    Select::make('primary_color')
                                        ->label('Primary color')
                                        ->columnSpanFull()
                                        ->options([
                                            Color::Blue[500] => 'Blue',
                                            Color::Emerald[500] => 'Emerald',
                                            Color::Gray[500] => 'Gray',
                                            Color::Orange[500] => 'Orange',
                                            Color::Rose[500] => 'Rose',
                                        ])
                                        ->helperText('Select primary color.')
                                        ->required(),
                                    FileUpload::make('logo')
                                        ->label('Logo')
                                        ->columnSpanFull()
                                        ->helperText('Upload a logo for the site.')
                                        ->required(),
                                ])
                            ]),
                        Tab::make('Path')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    TextInput::make('path')
                                        ->label('Path')
                                        ->prefix('/')
                                        ->columnSpanFull()
                                        ->helperText('Set starting path for the site, e.g. "/department"')
                                        ->required(),
                                    Toggle::make('trailing_slash')
                                        ->label('Always generate and redirect URLs using a trailing slash.')
                                        ->columnSpanFull()
                                        ->required(),
                                ])
                            ]),
                        Tab::make('Language & timezone')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    Select::make('default_country_code')
                                        ->label(__('Default country'))
                                        ->columnSpan(6)
                                        ->placeholder(fn() => Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 0 ? __('No countries available') : __('Select Country'))
                                        ->prefixIcon('heroicon-o-globe-europe-africa')
                                        ->options(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->get()->mapWithKeys(fn($language) => [
                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage($language->code, app()->getLocale())
                                        ])->sort())
                                        ->allowHtml()
                                        ->disabled(fn() => Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 0)
                                        ->default(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 1 ? Language::whereActive(1)->whereNotNull('country_code')->first()->country_code : null),
                                    Select::make('default_language_code')
                                        ->label(__('Default language'))
                                        ->columnSpan(6)
                                        ->placeholder(__('Select Language'))
                                        ->prefixIcon('heroicon-o-language')
                                        ->options(
                                            Language::whereActive(1)->get()->mapWithKeys(fn($language) => [
                                                $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage($language->code, app()->getLocale())
                                            ])->sort()
                                        )
                                        ->allowHtml()
                                        ->default(Language::whereActive(1)->count() === 1 ? Language::whereActive(1)->first()->code : Language::whereActive(1)->where('default', true)->first()?->code),
                                    Select::make('timezone')
                                        ->label('Timezone')
                                        ->columnSpanFull()
                                        ->helperText('Default timezone used for displaying date and time.')
                                        ->required(),
                                ])
                            ]),
                        Tab::make('Email')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    TextInput::make('email_from_name')
                                        ->label('Email From Name')
                                        ->columnSpan(6)
                                        ->helperText('Default name to use in email.')
                                        ->required(),
                                    TextInput::make('email_from_domain')
                                        ->label('Email From Domain')
                                        ->columnSpan(6)
                                        ->helperText('Default domain to use for sending email.')
                                        ->required(),
                                ])
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
