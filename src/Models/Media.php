<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }
}
