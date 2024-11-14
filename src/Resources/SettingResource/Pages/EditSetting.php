<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\Pages;

use Filament\Actions;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Vormkracht10\Backstage\Fields\Text;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Vormkracht10\Backstage\Fields\Textarea;
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
        if ($this->record->fields->count() === 0) {
            return $data;
        }

        foreach ($this->record->fields as $slug => $value) {
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
                                            ->columnSpanFull()
                                            ->label(__('Site')),
                                        Select::make('language_code')
                                            //     ->relationship('language', 'code')
                                            ->label(__('Language')),
                                        Select::make('country_code')
                                            // ->relationship('language', 'country_code')
                                            ->label(__('Country')),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private function getValueInputs(): array
    {
        $inputs = [];

        if ($this->record->fields->count() === 0) {
            return $inputs;
        }

        foreach ($this->record->fields as $field) {

            $input = match ($field->field_type) {
                'text' => Text::make(name: 'setting.' . $field->slug, field: $field),
                'textarea' => Textarea::make(name: 'setting.' . $field->slug, field: $field),
                'select' => Select::make('setting.' . $field->slug)
                    ->options($field->options),
                default => TextInput::make('setting.' . $field->slug),
            };

            $inputs[] = $input;
            // $inputs[] = $input->label(__($field->name))
            //     ->required($field->config['required'] ?? false)
            //     ->default($field->value);
        }

        return $inputs;
    }
}
