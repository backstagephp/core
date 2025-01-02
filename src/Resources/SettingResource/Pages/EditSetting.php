<?php

namespace Vormkracht10\Backstage\Resources\SettingResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Livewire\Attributes\On;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Vormkracht10\Backstage\Backstage;
use Filament\Forms\Components\Tabs\Tab;
use Vormkracht10\Backstage\Enums\Field;
use Vormkracht10\Backstage\Fields\Text;
use Filament\Resources\Pages\EditRecord;
use Vormkracht10\Backstage\Fields\Color;
use Vormkracht10\Backstage\Fields\Media;
use Vormkracht10\Backstage\Fields\Radio;
use Vormkracht10\Backstage\Fields\Toggle;
use Vormkracht10\Backstage\Fields\Checkbox;
use Vormkracht10\Backstage\Fields\DateTime;
use Vormkracht10\Backstage\Fields\KeyValue;
use Vormkracht10\Backstage\Fields\Repeater;
use Vormkracht10\Backstage\Fields\Textarea;
use Vormkracht10\Backstage\Fields\RichEditor;
use Vormkracht10\Backstage\Fields\CheckboxList;
use Vormkracht10\Backstage\Contracts\FieldInspector;
use Vormkracht10\Backstage\Resources\SettingResource;
use Vormkracht10\Backstage\Models\Field as FieldsModel;
use Vormkracht10\Backstage\Fields\Select as FieldsSelect;

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