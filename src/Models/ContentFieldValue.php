<?php

namespace Backstage\Models;

use Backstage\Fields\Models\Field;
use Illuminate\Support\HtmlString;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

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

    /**
     * Get the rich editor content as HTML using RichContentRenderer
     */
    public function getRichEditorHtml(): ?string
    {
        if ($this->field->field_type !== 'rich-editor') {
            return null;
        }

        $decoded = json_decode($this->value, true);
        
        // If it's already HTML, return it
        if (is_string($this->value) && !$decoded) {
            return $this->value;
        }
        
        // If it's JSON rich editor content, render it
        if (is_array($decoded) && isset($decoded['type']) && $decoded['type'] === 'doc') {
            return RichContentRenderer::make($decoded)->toHtml();
        }
        
        return null;
    }

    /**
     * Get the rich editor content as raw JSON
     */
    public function getRichEditorJson(): ?array
    {
        if ($this->field->field_type !== 'rich-editor') {
            return null;
        }

        $decoded = json_decode($this->value, true);
        
        if (is_array($decoded) && isset($decoded['type']) && $decoded['type'] === 'doc') {
            return $decoded;
        }
        
        return null;
    }

    /**
     * Update the rich editor content with new JSON data
     */
    public function updateRichEditorContent(array $newContent): bool
    {
        if ($this->field->field_type !== 'rich-editor') {
            return false;
        }

        // Validate that the content has the correct structure
        if (!isset($newContent['type']) || $newContent['type'] !== 'doc' || !isset($newContent['content'])) {
            throw new \InvalidArgumentException('Rich editor content must have type "doc" and content array');
        }

        $this->value = json_encode($newContent);
        return $this->save();
    }

    /**
     * Update the rich editor content by modifying existing content
     */
    public function modifyRichEditorContent(callable $modifier): bool
    {
        if ($this->field->field_type !== 'rich-editor') {
            return false;
        }

        $currentContent = $this->getRichEditorJson();
        
        if (!$currentContent) {
            // If no existing content, create a basic structure
            $currentContent = [
                'type' => 'doc',
                'content' => []
            ];
        }

        // Apply the modifier function
        $modifiedContent = $modifier($currentContent);
        
        // Validate the modified content
        if (!is_array($modifiedContent) || !isset($modifiedContent['type']) || $modifiedContent['type'] !== 'doc') {
            throw new \InvalidArgumentException('Modified content must be a valid rich editor document');
        }

        return $this->updateRichEditorContent($modifiedContent);
    }

}
