<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vormkracht10\Backstage\Shared\HasPackageFactory;

class FormSubmission extends Model
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

    public function site(): BelongsTo
    {
        return $this->BelongsTo(Site::class);
    }

    public function values()
    {
        return $this->hasMany(FormSubmissionValue::class, 'submission_ulid', 'ulid');
    }
}
