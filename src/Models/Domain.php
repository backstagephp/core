<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Scopes\ScopedBySite;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Vormkracht10\Backstage\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Domain extends Model
{
    use HasFactory;
    use HasUlids;
    use ScopedBySite;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    protected static function newFactory()
    {
        return DomainFactory::new();
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
