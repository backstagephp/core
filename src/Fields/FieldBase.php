<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;

abstract class FieldBase implements FieldInterface
{
    public function getForm(): array
    {
        return [
            Forms\Components\Toggle::make('config.required')
                ->label(__('Required'))
                ->inline(false),
        ];
    }

    public function getDefaultConfig(): array
    {
        return [
            'required' => false,
        ];
    }
}
