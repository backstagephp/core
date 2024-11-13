<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Resources\SettingResource;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->values as $slug => $value) {
            $data['setting'][$slug] = $value;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $fields = $data['setting'];
        unset($data['setting']);

        $data['values'] = $fields;

        return $data;
    }

    public function form(Form $form): Form
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
                                    ->columns(1)
                                    ->schema($this->getValueInputs()),
                            ]),
                        Tab::make('Configure')
                            ->label(__('Configure'))
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
                                            ->relationship('site', 'name')
                                            ->native(false)
                                            ->label(__('Site')),
                                        Select::make('author_id')
                                            ->relationship('author', 'name')
                                            ->native(false)
                                            ->default(auth()->id())
                                            ->label(__('Author')),
                                        Select::make('language_code')
                                            //     ->relationship('language', 'code')
                                            ->native(false)
                                            ->label(__('Language')),
                                        Select::make('country_code')
                                            // ->relationship('language', 'country_code')
                                            ->native(false)
                                            ->label(__('Country')),
                                        Select::make('fields')
                                            ->relationship('fields', 'slug')
                                            ->multiple()
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull()
                                            ->native(false)
                                            ->label(__('Fields')),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private function getValueInputs(): array
    {
        $inputs = [];

        foreach ($this->record->fields as $field) {

            $input = match ($field->field_type) {
                'text' => TextInput::make('setting.' . $field->slug),
                'select' => Select::make('setting.' . $field->slug)
                    ->options($field->options),
                default => TextInput::make('setting.' . $field->slug),
            };

            $inputs[] = $input->label(__($field->name))
                ->required($field->config['required'] ?? false)
                ->default($field->value);
        }

        return $inputs;
    }
}
