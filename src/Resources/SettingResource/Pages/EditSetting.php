<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\Pages;

use Locale;
use Filament\Actions;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs\Tab;
use Vormkracht10\Backstage\Fields\Text;
use Vormkracht10\Backstage\Models\Site;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Vormkracht10\Backstage\Fields\Textarea;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Fields\RichEditor;
use Vormkracht10\Backstage\Fields\Select as FieldsSelect;
use Vormkracht10\Backstage\Resources\SettingResource; // rename

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    #[On('refreshFields')]
    public function refresh(): void {}

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
                'rich-editor' => RichEditor::make(name: 'setting.' . $field->slug, field: $field),
                'select' => FieldsSelect::make('setting.' . $field->slug, $field),
                default => TextInput::make('setting.' . $field->slug),
            };

            $inputs[] = $input;
        }

        return $inputs;
    }
}
