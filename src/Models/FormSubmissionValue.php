<?php

namespace Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Backstage\Fields\Models\Field;
use Backstage\Shared\HasPackageFactory;

class FormSubmissionValue extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}
