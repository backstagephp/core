<?php

namespace Vormkracht10\Backstage\Resources\MenuResource\RelationManagers;

use Locale;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Fields\Select;
use Vormkracht10\Backstage\Models\Language;
use Filament\Resources\RelationManagers\RelationManager;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
