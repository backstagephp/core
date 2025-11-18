<?php

namespace Backstage\Resources;

use Backstage\Models\Language;
use Filament\Resources\Resource;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static bool $isScopedToTenant = false;
}
