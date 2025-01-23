<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\HtmlString;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Vormkracht10\Fields\Models\Field;

/**
 * Vormkracht10\Backstage\Models\ContentFieldValue
 *
 * @property string $value
 */
class ContentFieldValue extends Pivot
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content_field_values';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function value()
    {

        if (in_array($this->field->field_type, ['checkbox', 'radio', 'select']) && ! empty($this->field['config']['relations'])) {
            return Content::whereIn('ulid', json_decode($this->value))->get();
        }

        return json_decode($this->value, true) ?? new HtmlString($this->value);
    }
}
