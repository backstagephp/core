<?php

namespace Backstage\Models;

use Backstage\Fields\Models\Field;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\HtmlString;

/**
 * Backstage\Models\ContentFieldValue
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

    public function value(): Content | HtmlString | array | Collection | null
    {
        if (in_array($this->field->field_type, ['checkbox', 'radio', 'select']) && ! empty($this->field['config']['relations'])) {
            if (! json_validate($this->value)) {
                return Content::where('ulid', $this->value)->get();
            }

            return Content::whereIn('ulid', json_decode($this->value))->get();
        }

        $decoded = json_decode($this->value, true);

        // If the decoded value is an array (like blocks), return it as is
        if (is_array($decoded)) {
            return $decoded;
        }

        // For all other cases, ensure the value is returned as a string
        // This prevents automatic type casting of numeric values
        return new HtmlString($this->value ?? '');
    }
}
