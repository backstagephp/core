<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends \Backstage\Translations\Laravel\Models\Language
{
    use HasPackageFactory;

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class);
    }
}
