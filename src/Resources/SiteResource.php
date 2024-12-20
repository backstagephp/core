<?php

namespace Vormkracht10\Backstage\Resources;

use DateTimeZone;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Locale;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Resources\SiteResource\Pages;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-window';

    public static ?string $recordTitleAttribute = 'name';

    protected static bool $isScopedToTenant = false;

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

    public static function form(Form $form, bool $fullWidth = false): Form
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
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('The name of the site, used internally in the back-end only.')
                                        ->live(debounce: 250)
                                        ->afterStateUpdated(function (Set $set, ?string $state) {
                                            $set('slug', Str::slug($state));
                                            $set('name_plural', Str::plural($state));
                                        })
                                        ->required(),
                                    TextInput::make('slug')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('The slug for this site, used in code.')
                                        ->required(),
                                    TextInput::make('title')
                                        ->label('Title')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('The title of the site, used in the front-end for SEO.'),
                                    TextInput::make('title_separator')
                                        ->label('Title separator')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->default('|')
                                        ->hint('E.g. "Page Title | Name of site"')
                                        ->helperText('Symbol between page title and site name.'),
                                    Select::make('theme')
                                        ->label('Theme')
                                        ->columnSpanFull()
                                        ->helperText('Select default theme.'),
                                    Toggle::make('default')
                                        ->label('Use this site as default.')
                                        ->columnSpanFull(),
                                    Toggle::make('auth')
                                        ->label('Protect site behind authentication.')
                                        ->columnSpanFull(),
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
                                        ->preload()
                                        ->options([
                                            collect(Color::all())
                                                ->mapWithKeys(fn ($color, $name) => [
                                                    sprintf('#%02x%02x%02x', ...explode(', ', $color[500])) => ucfirst($name),
                                                ])
                                                ->put('#000000', 'Black')
                                                ->sort()
                                                ->unique()
                                                ->toArray(),
                                        ])
                                        ->helperText('Select primary color.')
                                        ->required(),
                                    FileUpload::make('logo')
                                        ->label('Logo')
                                        ->columnSpanFull()
                                        ->helperText('Upload a logo for the site.'),
                                ]),
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
                                        ->helperText('Set starting path for the site, e.g. "/department"'),
                                    Toggle::make('trailing_slash')
                                        ->label('Always generate and redirect URLs using a trailing slash.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Locale')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
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
                                                        $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' . $countryName . ')',
                                                    ])->toArray(),
                                                ])
                                        )
                                        ->allowHtml()
                                        ->visible(fn () => Language::where('active', 1)->count() > 1),

                                    Select::make('timezone')
                                        ->label('Timezone')
                                        ->columnSpanFull()
                                        ->searchable()
                                        ->preload()
                                        ->helperText('Default timezone used for displaying date and time.')
                                        ->options(
                                            collect([
                                                'Africa' => DateTimeZone::AFRICA,
                                                'America' => DateTimeZone::AMERICA,
                                                'Asia' => DateTimeZone::ASIA,
                                                'Europe' => DateTimeZone::EUROPE,
                                                'Oceania' => DateTimeZone::AUSTRALIA,
                                            ])->map(function ($code) {
                                                return collect(DateTimeZone::listIdentifiers($code))->mapWithKeys(fn ($code) => [$code => $code]);
                                            })
                                        )
                                        ->default(config('app.timezone'))
                                        ->required(),
                                ]),
                            ]),
                        Tab::make('Email')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    TextInput::make('email_from_name')
                                        ->label('Email From Name')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('Default name to use in email.')
                                        ->required(false),
                                    TextInput::make('email_from_domain')
                                        ->label('Email From Domain')
                                        ->columnSpan($fullWidth ? 12 : 6)
                                        ->helperText('Default domain to use for sending email.')
                                        ->required(false),
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
                ImageColumn::make('language_code')
                    ->label('Default language')
                    ->getStateUsing(fn (Site $record) => explode('-', $record->language_code)[0])
                    ->view('backstage::filament.tables.columns.language-flag-column'),
                ViewColumn::make('country_code')
                    ->label('Default country')
                    ->getStateUsing(fn (Site $record) => explode('-', $record->language_code)[1] ?? 'Worldwide')
                    ->view('backstage::filament.tables.columns.country-flag-column'),
                IconColumn::make('default')
                    ->label('Default')
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
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }
}
