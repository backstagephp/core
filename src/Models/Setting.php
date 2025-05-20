<?php

namespace Backstage\Models;

use Backstage\Fields\Concerns\HasFields;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Backstage\Models\Concerns\HasContentRelations;
/**
 * @property string $ulid
 * @property string $values
 * @property string $language_code
 * @property string $site_ulid
 */
class Setting extends Model
{
    use HasFields;
    use HasPackageFactory;
    use HasUlids;
    use HasContentRelations;

    protected $primaryKey = 'ulid';

    protected $table = 'settings';

    protected $guarded = [];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'code');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setting($key = null)
    {
        if (! $key) {

            return collect($this->values)->mapWithKeys(function ($value, $ulid) {
                $slug = $this->fields->where('ulid', $ulid)->first()?->slug ?? $ulid;

                return [$slug => $value];
            })
                ->toArray();
        }

        return $this->values[$this->fields->where('slug', $key)->first()?->ulid] ?? null;
    }
}
