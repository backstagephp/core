<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends Model
{
    use HasPackageFactory;

    protected $table = 'languages';

    protected $primaryKey = 'code';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $casts = [
        'code' => 'string',
        'name' => 'string',
        'native' => 'string',
        'active' => 'boolean',
        'default' => 'boolean',
    ];

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'domain_language', 'language_code', 'domain_ulid')
            ->withPivot('path');
    }

    public static function default(): ?Language
    {
        return static::firstWhere('default', true);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
