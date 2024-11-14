<?php

namespace Vormkracht10\Backstage\Fields;

use Vormkracht10\Backstage\Models\Field;

interface FieldInterface
{
    public function getForm(): array;

    public static function make(string $name, Field $field);
}
