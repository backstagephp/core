<?php

namespace Backstage\Models;

use Backstage\Models\Domain;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends \Backstage\Translations\Laravel\Models\Language
{
    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class);
    }
}
