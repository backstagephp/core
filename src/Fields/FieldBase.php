<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;

class FieldBase implements FieldInterface
{
    public function getForm(): array
    {
        return [
            Forms\Components\Toggle::make('config.required')
                ->label(__('Required'))
                ->inline(false)
        ];
    }
}