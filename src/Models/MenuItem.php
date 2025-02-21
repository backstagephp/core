<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class MenuItem extends Model
{
    use HasPackageFactory;
    use HasUlids;
    use HasRecursiveRelationships;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function content() {
        return $this->belongsTo(Content::class);
    }

    public function getParentKeyName()
    {
        return 'parent_ulid';
    }

    public function getLocalKeyName()
    {
        return 'ulid';
    }

    public function getPathName()
    {
        return 'parent_path';
    }
}
