<?php

namespace Backstage\Models;

class Language extends \Backstage\Translations\Models\Language
{
    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class);
    }
}
