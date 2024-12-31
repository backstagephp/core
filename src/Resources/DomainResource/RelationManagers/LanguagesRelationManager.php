<?php

namespace Vormkracht10\Backstage\Resources\DomainResource\RelationManagers;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;
use Locale;
use Vormkracht10\Backstage\Models\Language;

class LanguagesRelationManager extends RelationManager
{
    protected static string $relationship = 'languages';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Languages');
    }

    public static function getModelLabel(): string
    {
        return __('Language');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Languages');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        Section::make(__('Language'))
                            ->columns(3)
                            ->schema([
                                Select::make('language_code')
                                    ->label(__('Language'))
                                    ->columnSpanFull()
                                    ->placeholder(__('Select Language'))
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
                                TextInput::make('path')
                                    ->label(__('Path'))
                                    ->columnSpan(6),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('language')
            ->columns([
                ImageColumn::make('language_code')
                    ->label('Language')
                    ->getStateUsing(fn (Language $record) => explode('-', $record->language_code)[0])
                    ->view('backstage::filament.tables.columns.language-flag-column'),
                ViewColumn::make('country_code')
                    ->label('Country')
                    ->getStateUsing(fn (Language $record) => explode('-', $record->language_code)[1] ?? 'Worldwide')
                    ->view('backstage::filament.tables.columns.country-flag-column'),
                Tables\Columns\TextColumn::make('path')
                    ->label(__('Path'))
                    ->searchable()
                    ->limit(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshFields');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshFields');
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
