<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Vormkracht10\Fields\Concerns\HasFields;

class Setting extends Model
{
    use HasPackageFactory;
    use HasUlids;
    use HasFields;

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
}