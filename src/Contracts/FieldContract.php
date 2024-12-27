<?php

namespace Vormkracht10\Backstage\Contracts;

use Vormkracht10\Backstage\Models\Field;

interface FieldContract
{
    public function getForm(): array;

    public static function make(string $name, Field $field);

    public static function getDefaultConfig(): array;
}