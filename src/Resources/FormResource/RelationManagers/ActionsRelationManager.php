<?php

namespace Vormkracht10\Backstage\Resources\FormResource\RelationManagers;

use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;
use Locale;
use Vormkracht10\Backstage\Models\FormAction;
use Vormkracht10\Backstage\Models\Language;

class ActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'actions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Actions');
    }

    public static function getModelLabel(): string
    {
        return __('Action');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Actions');
    }

    public function form(Form $form): Form
    {
        $fields = $form->getLivewire()?->getOwnerRecord()?->fields?->pluck('name', 'slug') ?? collect();

        return $form
            ->schema([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        Section::make(__('Action'))
                            ->columns(3)
                            ->schema([
                                Select::make('type')
                                    ->label(__('Type'))
                                    ->columnSpan(6)
                                    ->options([
                                        'email' => __('Sent email'),
                                        // 'webhook' => __('Call webhook'), // Future - M
                                    ])
                                    ->default('email')
                                    ->required(),
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
                            ]),
                        Section::make(__('Configuration'))
                            ->columns(3)
                            ->schema([
                                TextInput::make('config.subject')
                                    ->label(__('Subject'))
                                    ->columnSpan(6),
                                Select::make('config.from_name')
                                    ->label(__('From name'))
                                    ->columnSpan(6)
                                    ->options($fields)
                                    ->searchable()
                                    ->searchDebounce(250)
                                    ->getSearchResultsUsing(fn (string $search): array => $fields->merge([$search => $search])->filter(fn ($field, $value) => str_contains($field, $search) || str_contains($value, $search))->toArray())
                                    ->native(false),
                                Select::make('config.from_email')
                                    ->label(__('Email from'))
                                    ->columnSpan(6)
                                    ->options($fields)
                                    ->searchable()
                                    ->searchDebounce(250)
                                    ->getSearchResultsUsing(fn (string $search): array => $fields->merge([$search => $search])->filter(fn ($field, $value) => str_contains($field, $search) || str_contains($value, $search))->toArray()),
                                Select::make('config.to_name')
                                    ->label(__('Name to'))
                                    ->columnSpan(6)
                                    ->options($fields)
                                    ->searchable()
                                    ->searchDebounce(250)
                                    ->getSearchResultsUsing(fn (string $search): array => $fields->merge([$search => $search])->filter(fn ($field, $value) => str_contains($field, $search) || str_contains($value, $search))->toArray()),
                                Select::make('config.to_email')
                                    ->label(__('Name email'))
                                    ->columnSpan(6)
                                    ->options($fields)
                                    ->searchable()
                                    ->searchDebounce(250)
                                    ->getSearchResultsUsing(fn (string $search): array => $fields->merge([$search => $search])->filter(fn ($field, $value) => str_contains($field, $search) || str_contains($value, $search))->toArray()),
                                Textarea::make('config.body')
                                    ->label(__('Body'))
                                    ->columnSpan(6),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->searchable()
                    ->limit(),
                Tables\Columns\TextColumn::make('config.subject')
                    ->label(__('Subject'))
                    ->description(fn (FormAction $record): string => ($record->config['from_name'] ?? '') . ' <' . ($record->config['from_email'] ?? '') . '>'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data) {
                        return [
                            ...$data,
                            'site_ulid' => Filament::getTenant()->ulid,
                        ];
                    })
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
