<?php

namespace Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentRelation extends Model
{
    protected $table = 'relationables';

    public $timestamps = false;

    protected $fillable = [
        'source_type',
        'source_ulid',
        'target_type',
        'target_ulid',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo('source', 'source_type', 'source_ulid');
    }

    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_ulid');
    }
} 