<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\ColorPicker as Input;
use Vormkracht10\Backstage\Enums\ColorFormat;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Contracts\FieldContract;

/**
 * For validation regex patterns, check the Filament documentation.
 *
 * @see https://filamentphp.com/docs/3.x/forms/fields/color-picker#color-picker-validation
 */
class Color extends FieldBase implements FieldContract
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'color' => ColorFormat::HEX->value,
            'regex' => null,
        ];
    }

    public static function make(string $name, Field $field): Input
    {
        $input = self::applyDefaultSettings(Input::make($name), $field);

        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->regex($field->config['regex'] ?? self::getDefaultConfig()['regex']);

        if ($field->config['color'] ?? self::getDefaultConfig()['color']) {
            $input->{$field->config['color']}(true);
        }

        return $input;
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
                    Forms\Components\Tabs\Tab::make('Field specific')
                        ->label(__('Field specific'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('config.color')
                                    ->label(__('Color format'))
                                    ->options(ColorFormat::array()),
                                Forms\Components\TextInput::make('config.regex')
                                    ->label(__('Regex'))
                                    ->placeholder(__('Enter a regex pattern')),
                            ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}