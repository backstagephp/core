<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\Checkbox as Input;
use Vormkracht10\Backstage\Models\Field;

class Checkbox extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'inline' => false,
            'accepted' => null,
            'declined' => null,
        ];
    }

    public static function make(string $name, Field $field): Input
    {
        $input = Input::make($name)
            ->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->required($field->config['required'] ?? self::getDefaultConfig()['required'])
            ->inline($field->config['inline'] ?? self::getDefaultConfig()['inline']);

        if ($field->config['accepted'] ?? self::getDefaultConfig()['accepted']) {
            $input->accepted($field->config['accepted']);
        }

        if ($field->config['declined'] ?? self::getDefaultConfig()['declined']) {
            $input->declined($field->config['declined']);
        }

        return $input;
    }

    public function getForm(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                ...parent::getForm(),
                Forms\Components\Toggle::make('config.inline')
                    ->label(__('Inline'))
                    ->inline(false),
                Forms\Components\Toggle::make('config.accepted')
                    ->label(__('Accepted'))
                    ->helperText(__('Requires the checkbox to be checked'))
                    ->inline(false),
                Forms\Components\Toggle::make('config.declined')
                    ->label(__('Declined'))
                    ->helperText(__('Requires the checkbox to be unchecked'))
                    ->inline(false),
            ]),
        ];
    }
}
