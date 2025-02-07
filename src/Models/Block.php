<?php

namespace Backstage\Models;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Backstage\Fields\Concerns\HasFields;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $ulid
 * @property string $slug
 * @property string $name
 * @property string $name_field
 * @property string $icon
 * @property string $component
*/
class Block extends Model
{
    use HasFields;
    use HasUlids;
    use HasPackageFactory;

    protected $primaryKey = 'ulid';

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
