<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;

class Text extends FieldBase implements FieldInterface
{
    public function getForm(): array
    {
        return [
            ...parent::getForm(),
        ];
    }
}