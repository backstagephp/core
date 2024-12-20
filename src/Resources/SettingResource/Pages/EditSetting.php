<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use Vormkracht10\Backstage\Fields\Checkbox;
use Vormkracht10\Backstage\Fields\CheckboxList;
use Vormkracht10\Backstage\Fields\Color;
use Vormkracht10\Backstage\Fields\DateTime;
use Vormkracht10\Backstage\Fields\KeyValue;
use Vormkracht10\Backstage\Fields\Media;
use Vormkracht10\Backstage\Fields\Radio;
use Vormkracht10\Backstage\Fields\RichEditor;
use Vormkracht10\Backstage\Fields\Select as FieldsSelect;
use Vormkracht10\Backstage\Fields\Text;
use Vormkracht10\Backstage\Fields\Textarea;
use Vormkracht10\Backstage\Fields\Toggle;
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

        foreach ($this->record->fields as $field) {
            $data['setting'][$field->slug] = $this->record->values[$field->slug] ?? null;
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
                                    ->schema(
                                        SettingResource::fields(),
                                    ),
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
                'select' => FieldsSelect::make(name: 'setting.' . $field->slug, field: $field),
                'checkbox' => Checkbox::make(name: 'setting.' . $field->slug, field: $field),
                'checkbox-list' => CheckboxList::make(name: 'setting.' . $field->slug, field: $field),
                'media' => Media::make(name: 'setting.' . $field->slug, field: $field),
                'key-value' => KeyValue::make(name: 'setting.' . $field->slug, field: $field),
                'radio' => Radio::make(name: 'setting.' . $field->slug, field: $field),
                'toggle' => Toggle::make(name: 'setting.' . $field->slug, field: $field),
                'color' => Color::make(name: 'setting.' . $field->slug, field: $field),
                'datetime' => DateTime::make(name: 'setting.' . $field->slug, field: $field),
                default => TextInput::make(name: 'setting.' . $field->slug),
            };

            $inputs[] = $input;
        }

        return $inputs;
    }
}
