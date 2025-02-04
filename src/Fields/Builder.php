<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\Builder as Input;
use Filament\Forms\Components\Builder\Block as BuilderBlock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vormkracht10\Backstage\Contracts\FieldContract;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Fields\Concerns\CanMapDynamicFields;
use Vormkracht10\Fields\Fields;
use Vormkracht10\Fields\Fields\Base;
use Vormkracht10\Fields\Models\Field;

class Builder extends Base implements FieldContract
{
    use CanMapDynamicFields;

    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
        ];
    }

    public static function make(string $name, ?Field $field = null): Input
    {
        $input = self::applyDefaultSettings(
            Input::make($name)
                ->blocks(
                    self::getBlockOptions()
                ),
            $field
        );

        return $input;
    }

    /**
     * Get the blocks
     */
    private static function getBlockOptions()
    {
        $blocks = Block::with('fields')->get();
        $options = [];

        foreach ($blocks as $block) {
            $options[] = BuilderBlock::make($block->slug)
                ->icon($block->icon ? 'heroicon-o-' . $block->icon : null)
                ->schema(
                    self::resolveFormFields($block)
                );
        }

        return $options;
    }

    public function getForm(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->schema([
                    Forms\Components\Tabs\Tab::make('General')
                        ->label(__('General'))
                        ->schema([
                            ...parent::getForm(),
                        ]),
                ])->columnSpanFull(),
        ];
    }

    // TODO: Get this from the package
    private static function resolveFormFields(mixed $record = null): array
    {
        if (! isset($record->fields) || $record->fields->isEmpty()) {
            return [];
        }

        $customFields = self::resolveCustomFields();

        return $record->fields
            ->map(fn ($field) => self::resolveFieldInput($field, $customFields, $record))
            ->filter()
            ->values()
            ->all();
    }

    // TODO: Get this from the package
    private static function resolveCustomFields(): Collection
    {
        return collect(Fields::getFields())
            ->map(fn ($fieldClass) => new $fieldClass);
    }

    // TODO: Get this from the package
    private static function resolveFieldInput(Model $field, Collection $customFields, Model $record): ?object
    {
        $inputName = "{$record->valueColumn}.{$field->ulid}";

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
