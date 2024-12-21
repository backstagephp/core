<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\FileUploadcare\Forms\Components\FileUploadcare as Input;

class FileUploadcare extends FieldBase implements FieldInterface
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
                                //
                            ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}