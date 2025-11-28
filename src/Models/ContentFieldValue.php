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
        if ($this->field->hasRelation()) {
            return static::getContentRelation($this->value);
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
                $decoded = $this->decodeAllJsonStrings($decoded);

                if ($this->field->field_type === 'repeater') {
                    $decoded = $this->hydrateRepeaterRelations($decoded);
                }

                return $decoded;
            } else {
                return $decoded;
            }

        }

        // For all other cases, ensure the value is returned as a string
        // This prevents automatic type casting of numeric values
        return new HtmlString($this->value ?? '');
    }

    /**
     * Get the relation value
     */
    public static function getContentRelation(mixed $value): Content | Collection | null
    {
        if (! json_validate($value)) {
            return Content::where('ulid', $value)->first();
        }

        $ulids = json_decode($value);

        return Content::whereIn('ulid', $ulids)
            ->orderByRaw('FIELD(ulid, ' . implode(',', array_fill(0, count($ulids), '?')) . ')', $ulids)
            ->get();
    }

    private function isRichEditor(): bool
    {
        return $this->field->field_type === 'rich-editor';
    }

    private function isCheckbox(): bool
    {
        return $this->field->field_type === 'checkbox';
    }

    private function isJsonArray(): ?array
    {
        $decoded = json_decode($this->value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get the rich editor content as HTML using RichContentRenderer
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

    private function hydrateRepeaterRelations(array $data): array
    {
        $relationFields = $this->getRelationFields();

        if ($relationFields->isEmpty()) {
            return $data;
        }

        foreach ($relationFields as $field) {
            $values = $this->collectRelationValues($data, $field);

            if (empty($values)) {
                continue;
            }

            $loadedModels = $this->loadRelatedModels($field, $values);

            $data = $this->hydrateRows($data, $field, $loadedModels);
        }

        return $data;
    }

    private function getRelationFields(): Collection
    {
        if (! $this->field->relationLoaded('children')) {
            $this->field->load('children');
        }

        $childFields = $this->field->children;

        if ($childFields->isEmpty()) {
            return new Collection;
        }

        return $childFields->filter(fn (Field $field) => $field->hasRelation());
    }

    private function collectRelationValues(array $data, Field $field): array
    {
        $values = [];

        foreach ($data as $row) {
            if (isset($row[$field->slug])) {
                $val = $row[$field->slug];
                if (is_array($val)) {
                    $values = array_merge($values, $val);
                } elseif (is_string($val)) {
                    $values[] = $val;
                }
            }
        }

        return array_unique($values);
    }

    private function loadRelatedModels(Field $field, array $values): Collection
    {
        $relations = $field->config['relations'] ?? [];
        $loadedModels = new Collection;

        foreach ($relations as $relation) {
            $resource = $relation['resource'] ?? null;
            if (! $resource) {
                continue;
            }

            $modelClass = $this->resolveResourceModelClass($resource);
            if (! $modelClass) {
                continue;
            }

            $keyName = $relation['relationKey'] ?? (new $modelClass)->getKeyName();

            $results = $modelClass::whereIn($keyName, $values)->get();
            $loadedModels = $loadedModels->merge($results);
        }

        return $loadedModels;
    }

    private function hydrateRows(array $data, Field $field, Collection $loadedModels): array
    {
        foreach ($data as $rowIndex => $row) {
            if (! isset($row[$field->slug])) {
                continue;
            }

            $val = $row[$field->slug];

            if (is_array($val)) {
                $hydrated = new Collection;
                foreach ($val as $v) {
                    $match = $loadedModels->first(fn ($m) => $m->getAttribute($m->getKeyName()) == $v);
                    if ($match) {
                        $hydrated->push($match);
                    }
                }
                $data[$rowIndex][$field->slug] = $hydrated;
            } else {
                $match = $loadedModels->first(fn ($m) => $m->getAttribute($m->getKeyName()) == $val);
                if ($match) {
                    $data[$rowIndex][$field->slug] = $match;
                }
            }
        }

        return $data;
    }

    private function resolveResourceModelClass(string $resource): ?string
    {
        if (class_exists($resource)) {
            return $resource;
        }

        $resources = config('backstage.fields.selectable_resources', []);

        foreach ($resources as $res) {
            if (! class_exists($res)) {
                continue;
            }
            $instance = new $res;
            if (method_exists($instance, 'getModel')) {
                $modelClass = $instance->getModel();
                if (class_exists($modelClass)) {
                    $model = new $modelClass;
                    if ($model->getTable() === $resource) {
                        return $modelClass;
                    }
                }
            }
        }

        return null;
    }
}
