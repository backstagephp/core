<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Vormkracht10\Backstage\Shared\HasPackageFactory;

class Tag extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    public function content(): MorphToMany
    {
        return $this->morphedByMany(Content::class, 'taggable', 'taggables', 'taggable_ulid', 'tag_ulid');
    }

    public function site(): MorphToMany
    {
        return $this->morphedByMany(Site::class, 'taggable', 'taggables', 'taggable_ulid', 'tag_ulid');
    }
}
