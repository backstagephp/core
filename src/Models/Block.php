<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Vormkracht10\Fields\Concerns\HasFields;

class Block extends Model
{
    use HasFields;
    use HasPackageFactory;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function render(): HtmlString
    {
        return new Htmlstring(
            Blade::render("<x-{$this->slug} :attributes='' />")
        );
    }
}
