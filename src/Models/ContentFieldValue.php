<?php

namespace Backstage\Models;

use Backstage\Concerns\DecodesJsonStrings;
use Backstage\Fields\Models\Field;
use Backstage\Fields\Plugins\JumpAnchorRichContentPlugin;
use Backstage\Shared\HasPackageFactory;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\HtmlString;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Backstage\Models\ContentFieldValue
 *
 * @property string $value
 */
class ContentFieldValue extends Pivot
{
    use DecodesJsonStrings;
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

    public function value(): Content | HtmlString | array | Collection | bool | null
    {
        if ($this->hasRelation()) {
            return $this->getContentRelation();
        }

        if ($this->isRichEditor()) {
            return new HtmlString(self::getRichEditorHtml($this->value)) ?? new HtmlString('');
        }

        if ($this->isCheckbox()) {
            return $this->value == '1';
        }

        if ($decoded = $this->isJsonArray()) {
            // For repeater and builder fields, use recursive decoding
            if (in_array($this->field->field_type, ['repeater', 'builder'])) {
                return $this->decodeAllJsonStrings($decoded);
            }
            else {
                return $decoded;
            }

        }

        // For all other cases, ensure the value is returned as a string
        // This prevents automatic type casting of numeric values
        return new HtmlString($this->value ?? '');
    }

    /**
     * Check if the field has a relation
     * @return bool
     */
    private function hasRelation(): bool
    {
        return in_array($this->field->field_type, ['checkbox-list', 'radio', 'select']) && ! empty($this->field['config']['relations']);
    }

    /**
     * Get the relation value
     * @return Content | Collection
     */
    private function getContentRelation(): Content | Collection
    {
        if (! json_validate($this->value)) {
            return Content::where('ulid', $this->value)->get();
        }

        return Content::whereIn('ulid', json_decode($this->value))->get();
    }

    private function isRichEditor(): bool
    {
        return $this->field->field_type === 'rich-editor';
    }

    private function isCheckbox(): bool
    {
        return $this->field->field_type === 'checkbox';
    }

    private function isJsonArray(): array | null
    {
        $decoded = json_decode($this->value, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get the rich editor content as HTML using RichContentRenderer
     * 
     * @param array | string $value
     * @return string | null
     */
    public static function getRichEditorHtml(array | string $value): ?string
    {
        if (is_array($value)) {
            $decoded = $value;
        } else {
            $decoded = json_decode($value, true);
        }

        // If it's already HTML, return it
        if (is_string($value) && ! $decoded) {
            return $value;
        }

        // If it's JSON rich editor content, render it
        if (is_array($decoded) && isset($decoded['type']) && $decoded['type'] === 'doc') {
            return RichContentRenderer::make($decoded)
                ->plugins([JumpAnchorRichContentPlugin::get()])
                ->toHtml();
        }

        return null;
    }
}
