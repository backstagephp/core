<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Content extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'meta_tags' => 'array',
        ];
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(Field::class, 'content_meta', 'content_ulid', 'field_ulid')
            ->using(ContentMeta::class)
            ->withPivot('value');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(ContentMeta::class, 'content_ulid', 'ulid');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, ['code', 'country_code']);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
