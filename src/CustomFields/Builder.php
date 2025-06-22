<?php

namespace Backstage\CustomFields;

use Filament\Forms;
use Backstage\Models\Block;
use Backstage\Fields\Fields\Base;
use Backstage\Fields\Models\Field;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Hidden;
use Backstage\Contracts\FieldContract;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder as Input;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Filament\Forms\Components\Builder\Block as BuilderBlock;

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
                ->collapsed(true)
                ->blockPreviews(areInteractive: true)
                ->collapsible()
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
                ->label($block->name)
                ->icon($block->icon ? 'heroicon-o-' . $block->icon : null)
                ->preview(self::getPreviewTemplate($block))
                ->schema([
                    ...self::resolveFormFields(record: $block, isNested: true),
                    TextInput::make('block_ulid')
                        ->default($block->ulid)
                        ->hidden()
                        ->dehydrated()
                        ->afterStateHydrated(function ($state, $set) use ($block) {
                            if (empty($state)) {
                                $set('block_ulid', $block->ulid);
                            }
                        }),
                ]);
        }

        return $options;
    }

    /**
     * Get the preview template for a block
     */
    private static function getPreviewTemplate(Block $block): string
    {
        return 'backstage::field-previews.generic-block';
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

    private static function resolveFormFields(mixed $record = null, bool $isNested = false): array
    {
        $instance = new self;

        $isNested = true; // Builder fields are always nested

        return $instance->traitResolveFormFields(record: $record, isNested: $isNested);
    }

    private static function resolveCustomFields(): Collection
    {
        $instance = new self;

        return $instance->traitResolveCustomFields();
    }

    private static function resolveFieldInput(Field $field, Collection $customFields, mixed $record = null, bool $isNested = false): ?object
    {
        $instance = new self;

        $isNested = true; // Builder fields are always nested

        return $instance->traitResolveFieldInput(field: $field, customFields: $customFields, record: $record, isNested: $isNested);
    }
}
