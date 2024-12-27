<?php

namespace Vormkracht10\Backstage\Concerns;

use Illuminate\Support\Str;
use Vormkracht10\Backstage\Backstage;

trait HasConfigurableFields
{
    private function initializeDefaultConfig(string $fieldType): array
    {
        $className = 'Vormkracht10\\Backstage\\Fields\\' . Str::studly($fieldType);

        if (! class_exists($className)) {
            return [];
        }

        $fieldInstance = app($className);

        return $fieldInstance::getDefaultConfig();
    }

    private function initializeCustomConfig(string $fieldType): array
    {
        $className = Backstage::getFields()[$fieldType] ?? null;

        if (! class_exists($className)) {
            return [];
        }

        $fieldInstance = app($className);

        return $fieldInstance::getDefaultConfig();
    }

    private function formatCustomFields(array $fields): array
    {
        return collect($fields)->mapWithKeys(function ($field, $key) {
            $parts = explode('\\', $field);
            $lastPart = end($parts);
            $formattedName = ucwords(str_replace('_', ' ', strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $lastPart))));

            return [$key => $formattedName];
        })->toArray();
    }
}
