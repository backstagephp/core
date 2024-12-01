<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vormkracht10\Backstage\Factories\DomainFactory;

class Domain extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
