<?php

namespace Backstage\CustomFields;

use Backstage\Contracts\FieldContract;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Fields\Fields\Base;
use Backstage\Fields\Models\Field;
use Backstage\Models\Block;
use Filament\Forms;
use Filament\Forms\Components\Builder as Input;
use Filament\Forms\Components\Builder\Block as BuilderBlock;
use Illuminate\Support\Collection;

class Builder extends Base implements FieldContract
{
    use CanMapDynamicFields {
        resolveFormFields as private traitResolveFormFields;
        resolveCustomFields as private traitResolveCustomFields;
        resolveFieldInput as private traitResolveFieldInput;
    }

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
                ->label($field->name)
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

    private static function resolveFormFields(mixed $record = null): array
    {
        // Create an instance of the class to call the non-static trait method
        $instance = new static;

        return $instance->traitResolveFormFields(record: $record);
    }

    private static function resolveCustomFields(): Collection
    {
        // Create an instance of the class to call the non-static trait method
        $instance = new static;

        return $instance->traitResolveCustomFields();
    }

    private static function resolveFieldInput(Field $field, Collection $customFields, mixed $record = null): ?object
    {
        // Create an instance of the class to call the non-static trait method
        $instance = new static;

        return $instance->traitResolveFieldInput(field: $field, customFields: $customFields, record: $record);
    }
}
