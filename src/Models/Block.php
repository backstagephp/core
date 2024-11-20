<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Block extends Model
{
    use HasFactory,
        HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function fields(): MorphToMany
    {
        return $this->morphToMany(Field::class, 'fieldable');
    }
}
