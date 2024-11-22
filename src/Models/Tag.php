<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Scopes\ScopedBySite;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;
    use HasUlids;
    use ScopedBySite;

    protected $primaryKey = 'ulid';

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
