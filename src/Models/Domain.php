<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory,
        HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }
}
