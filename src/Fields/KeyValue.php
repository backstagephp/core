<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\KeyValue as Input;
use Vormkracht10\Backstage\Models\Field;

class KeyValue extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
        ];
    }

    public static function make(string $name, Field $field): Input
    {
        $input = self::applyDefaultSettings(Input::make($name), $field);

        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null);

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

                                // Forms\Components\Toggle::make('config.inline')
                                //     ->label(__('Inline'))
                                //     ->inline(false),
                                // Forms\Components\Toggle::make('config.accepted')
                                //     ->label(__('Accepted'))
                                //     ->helperText(__('Requires the checkbox to be checked'))
                                //     ->inline(false),
                                // Forms\Components\Toggle::make('config.declined')
                                //     ->label(__('Declined'))
                                //     ->helperText(__('Requires the checkbox to be unchecked'))
                                //     ->inline(false),
                            ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}