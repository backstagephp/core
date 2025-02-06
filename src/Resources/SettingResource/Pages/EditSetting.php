<?php

namespace Backstage\Resources\SettingResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Backstage\Backstage;
use Backstage\Contracts\FieldInspector;
use Backstage\Enums\Field;
use Backstage\Fields\Checkbox;
use Backstage\Fields\CheckboxList;
use Backstage\Fields\Color;
use Backstage\Fields\DateTime;
use Backstage\Fields\KeyValue;
use Backstage\Fields\Media;
use Backstage\Fields\Radio;
use Backstage\Fields\Repeater;
use Backstage\Fields\RichEditor;
use Backstage\Fields\Select as FieldsSelect;
use Backstage\Fields\Tags;
use Backstage\Fields\Text;
use Backstage\Fields\Textarea;
use Backstage\Fields\Toggle;
use Backstage\Models\Field as FieldsModel;
use Backstage\Resources\SettingResource;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    private FieldInspector $fieldInspector;

    private const FIELD_TYPE_MAP = [
        'text' => Text::class,
        'textarea' => Textarea::class,
        'rich-editor' => RichEditor::class,
        'repeater' => Repeater::class,
        'select' => FieldsSelect::class,
        'checkbox' => Checkbox::class,
        'checkbox-list' => CheckboxList::class,
        'media' => Media::class,
        'key-value' => KeyValue::class,
        'radio' => Radio::class,
        'toggle' => Toggle::class,
        'color' => Color::class,
        'datetime' => DateTime::class,
        'tags' => Tags::class,
    ];

    public function boot(): void
    {
        $this->fieldInspector = app(FieldInspector::class);
    }

    #[On('refreshFields')]
    public function refresh(): void
    {
        //
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->fields->isEmpty()) {
            return $data;
        }

        return $this->mutateFormData($data, function ($field, $fieldConfig, $fieldInstance, $data) {
            if (! empty($fieldConfig['methods']['mutateFormDataCallback'])) {
                return $fieldInstance->mutateFormDataCallback($this->record, $field, $data);
            }

            $data['setting'][$field->slug] = $this->record->values[$field->slug] ?? null;

            return $data;
        });
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->mutateFormData($data, function ($field, $fieldConfig, $fieldInstance, $data) {
            if (! empty($fieldConfig['methods']['mutateBeforeSaveCallback'])) {
                return $fieldInstance->mutateBeforeSaveCallback($this->record, $field, $data);
            }

            return $data;
        });

        // Move settings to values
        $fields = $data['setting'] ?? [];
        unset($data['setting']);
        $data['values'] = $fields;

        return $data;
    }

    protected function mutateFormData(array $data, callable $mutationStrategy): array
    {
        foreach ($this->record->fields as $field) {
            $fieldConfig = Field::tryFrom($field->field_type)
                ? $this->fieldInspector->initializeDefaultField($field->field_type)
                : $this->fieldInspector->initializeCustomField($field->field_type);

            $fieldInstance = new $fieldConfig['class'];
            $data = $mutationStrategy($field, $fieldConfig, $fieldInstance, $data);
        }

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
                                    ->schema($this->resolveFormFields()),
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

    private function resolveFormFields(): array
    {
        if ($this->record->fields->isEmpty()) {
            return [];
        }

        $customFields = $this->resolveCustomFields();

        return $this->record->fields
            ->map(fn($field) => $this->resolveFieldInput($field, $customFields))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveCustomFields(): Collection
    {
        return collect(Backstage::getFields())
            ->map(fn($fieldClass) => new $fieldClass);
    }

    private function resolveFieldInput(FieldsModel $field, Collection $customFields): ?object
    {
        $inputName = "setting.{$field->slug}";

        // Try to resolve from standard field type map
        if ($fieldClass = self::FIELD_TYPE_MAP[$field->field_type] ?? null) {
            return $fieldClass::make(name: $inputName, field: $field);
        }

        // Try to resolve from custom fields
        if ($customField = $customFields->get($field->field_type)) {
            return $customField::make($inputName, $field);
        }

        return null;
    }
}
