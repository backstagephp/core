<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;

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
}
