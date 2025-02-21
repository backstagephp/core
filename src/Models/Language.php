<?php

namespace Backstage\Models;

use Backstage\Models\Domain;

class Language extends \Backstage\Translations\Laravel\Models\Language
{
    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class);
    }
}
