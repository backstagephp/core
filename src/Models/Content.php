<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Content extends Model
{
    use HasFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, ['code', 'country_code']);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'content_type', 'slug');
    }

    public function values(): HasMany
    {
        return $this->hasMany(Meta::class, 'content_ulid', 'ulid');
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'meta', 'content_ulid', 'field_ulid')
            ->using(Meta::class)
            ->withPivot('value');
    }
}
