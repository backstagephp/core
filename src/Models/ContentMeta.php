<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ContentMeta extends Pivot
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content_meta';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
