<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Factories\DomainFactory;

class Domain extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $guarded = [];

    protected static function newFactory()
    {
        return DomainFactory::new();
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }
}
