<?php

namespace Vormkracht10\Fields\Fields;

use Filament\Forms;
use Filament\Forms\Components\Builder as Input;
use Filament\Forms\Components\Builder\Block as BuilderBlock;
use Vormkracht10\Backstage\Contracts\FieldContract;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Models\Field;

class Builder extends FieldBase implements FieldContract
{
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
                    $block->fields->map(function ($field) {
                        return match ($field->field_type) {
                            'text' => Text::make($field->slug, $field)
                                ->label($field->name),
                            'checkbox' => Checkbox::make($field->slug, $field)
                                ->label($field->name),
                            'rich-editor' => RichEditor::make($field->slug, $field)
                                ->label($field->name),
                            'textarea' => Textarea::make($field->slug, $field)
                                ->label($field->name),
                            'select' => Select::make($field->slug, $field)
                                ->label($field->name)
                                ->options($field->config['options'] ?? null),
                            'builder' => Builder::make($field->slug, $field)
                                ->label($field->name),
                            default => Text::make($field->slug, $field)
                                ->label($field->name),
                        };
                    })->toArray()
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
}
