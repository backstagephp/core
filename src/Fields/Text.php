<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;

class Text
{
    public function getForm(): array
    {
        return [
            Forms\Components\Toggle::make('config.required')
                ->label(__('Required')),
        ];
    }
}
