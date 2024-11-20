<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meta extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'meta';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function fields(): belongsTo
    {
        return $this->belongsTo(Field::class, 'field_ulid');
    }
}
