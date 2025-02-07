<?php

namespace Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Backstage\Shared\HasPackageFactory;

class Domain extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'domain_language', 'domain_ulid', 'language_code')
            ->withPivot('path');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
