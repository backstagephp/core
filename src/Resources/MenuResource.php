<?php

namespace Backstage\Resources;

use Locale;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Backstage\Models\Menu;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Backstage\Models\Language;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Backstage\Resources\MenuResource\Pages;
use Backstage\Resources\MenuResource\RelationManagers\MenuItemsRelationManager;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Structure');
    }

    public static function getModelLabel(): string
    {
        return __('Menu');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Menus');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Menu')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Hidden::make('language_code')
                                            ->default(Language::active()->count() === 1 ? Language::active()->first()?->code : Language::active()->where('default', true)->first()?->code),
                                            
                                        Select::make('language_code')
                                            ->label(__('Language'))
                                            ->columnSpanFull()
                                            ->placeholder(__('Select Language'))
                                            ->options(
                                                Language::active()
                                                    ->get()
                                                    ->sort()
                                                    ->groupBy(function ($language) {
                                                        return Str::contains($language->code, '-') ? getLocalizedCountryName($language->code) : __('Worldwide');
                                                    })
                                                    ->mapWithKeys(fn ($languages, $countryName) => [
                                                        $countryName => $languages->mapWithKeys(fn ($language) => [
                                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ' . getLocalizedLanguageName($language->code) . ' (' . $countryName . ')',
                                                        ])->toArray(),
                                                    ])
                                            )
                                            ->allowHtml()
                                            ->visible(fn () => Language::active()->count() > 1),

                                        TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Menu $record) {
                                                $currentSlug = $get('slug');

                                                if (! $record?->slug && (! $currentSlug || $currentSlug === Str::slug($old))) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true),
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
            MenuItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
