<?php

namespace Vormkracht10\Backstage\Models;

use Vormkracht10\Fields\Models\Field;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentFieldValue extends Pivot
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content_field_values';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}
