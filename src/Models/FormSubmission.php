<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory,
        HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }
}
