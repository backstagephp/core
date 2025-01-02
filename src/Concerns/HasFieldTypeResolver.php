<?php

namespace Vormkracht10\Backstage\Concerns;

use Exception;
use Illuminate\Support\Str;
use Vormkracht10\Backstage\Enums\Field as EnumsField;
use Vormkracht10\Backstage\Facades\Backstage;

trait HasFieldTypeResolver
{
    /** @throws Exception If the field type class cannot be resolved. */
    protected function getFieldTypeFormSchema(?string $fieldType): array
    {
        if (empty($fieldType)) {
            return [];
        }

        try {
            $className = $this->resolveFieldTypeClassName($fieldType);

            if (! $this->isValidFieldClass($className)) {
                return [];
            }

            return app($className)->getForm();
        } catch (Exception $e) {
            throw new Exception("Failed to resolve field type class for '{$fieldType}'");
        }
    }

    protected static function resolveFieldTypeClassName(string $fieldType): ?string
    {
        if (EnumsField::tryFrom($fieldType)) {
            return sprintf('Vormkracht10\\Backstage\\Fields\\%s', Str::studly($fieldType));
        }

        return Backstage::getFields()[$fieldType] ?? null;
    }

    protected function isValidFieldClass(?string $className): bool
    {
        return $className !== null
            && class_exists($className)
            && method_exists($className, 'getForm');
    }
}
