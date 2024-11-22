<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vormkracht10\Backstage\Scopes\ScopedBySite;

class Media extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
