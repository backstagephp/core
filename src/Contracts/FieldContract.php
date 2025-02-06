<?php

namespace Backstage\Contracts;

use Backstage\Models\Field;

interface FieldContract
{
    public function getForm(): array;

    public static function make(string $name, Field $field);

    public static function getDefaultConfig(): array;
}
