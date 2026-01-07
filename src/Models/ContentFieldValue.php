<?php

namespace Backstage\Models;

use Backstage\Concerns\DecodesJsonStrings;
use Backstage\Fields\Models\Field;
use Backstage\Fields\Plugins\JumpAnchorRichContentPlugin;
use Backstage\Shared\HasPackageFactory;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
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

    public function contentRelation(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_ulid');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(config('backstage.media.model', \Backstage\Media\Models\Media::class), 'model', 'media_relationships', 'model_id', 'media_ulid')
            ->withPivot(['position', 'meta']);
    }

    public function getHydratedValue(): Content | HtmlString | array | Collection | bool | string | Model | null
    {
        if ($this->isRichEditor()) {
            $html = self::getRichEditorHtml($this->value ?? '') ?? '';

            return $this->shouldHydrate() ? new HtmlString($html) : $html;
        }

        $shouldHydrate = $this->shouldHydrate();
        // TODO (IMPORTANT): This should be fixed in the Uploadcare package itself.
        $isUploadcare = $this->field->field_type === 'uploadcare';

        if ($shouldHydrate || $isUploadcare) {
            [$hydrated, $result] = $this->tryHydrateViaClass($this->isJsonArray(), $this->field->field_type, $this->field);

            if ($hydrated) {
                return $result;
            }
        }

        if ($this->shouldHydrate() && $this->field->hasRelation()) {
            return static::getContentRelation($this->value);
        }

        if ($this->isCheckbox()) {
            return $this->value == '1';
        }

        if ($decoded = $this->isJsonArray()) {
            if (in_array($this->field->field_type, ['repeater', 'builder'])) {
                return $this->hydrateValuesRecursively($decoded, $this->field);
            }

            return $decoded;
        }

        // For all other cases, ensure the value is returned as a string (HTML string in frontend)
        $val = $this->value ?? '';
        $res = $this->shouldHydrate() ? new HtmlString($val) : $val;

        return $res;
    }

    /**
     * Get the relation value
     */
    public static function getContentRelation(mixed $value): Content | Collection | null
    {
        if (is_array($value)) {
            $ulids = $value;
        } elseif (is_string($value) && json_validate($value)) {
            $ulids = json_decode($value, true);
        } else {
            return Content::where('ulid', $value)->first();
        }

        if (empty($ulids)) {
            return new Collection;
        }

        return Content::whereIn('ulid', $ulids)
            ->orderByRaw('FIELD(ulid, ' . implode(',', array_fill(0, count($ulids), '?')) . ')', $ulids)
            ->get();
    }

    private function hydrateValuesRecursively(mixed $value, Field $field): mixed
    {
        // Handle case where Content relationship was incorrectly loaded for rich-editor fields with slug 'content'
        if ($field->field_type === 'rich-editor' && $value instanceof \Backstage\Models\Content) {
            return ''; // Reset to empty string as rich-editor shouldn't store Content objects
        }

        if ($this->shouldHydrate()) {
            [$hydrated, $result] = $this->tryHydrateViaClass($value, $field->field_type, $field);
            if ($hydrated) {
                return $result;
            }
        }

        if ($this->shouldHydrate() && $field->hasRelation()) {
            return static::getContentRelation($value);
        }

        if (is_array($value) && in_array($field->field_type, ['repeater', 'builder'])) {
            if ($field->field_type === 'repeater') {
                if (! $field->relationLoaded('children')) {
                    $field->load('children');
                }

                if ($field->children->isEmpty()) {
                    return $value;
                }

                foreach ($value as $index => &$item) {
                    if (! is_array($item)) {
                        continue;
                    }
                    $this->hydrateItemFields($item, $field->children);
                }
                unset($item);
            } elseif ($field->field_type === 'builder') {
                static $blockCache = [];

                foreach ($value as $index => &$item) {
                    if (! isset($item['type'], $item['data']) || ! is_array($item['data'])) {
                        continue;
                    }

                    $blockSlug = $item['type'];

                    if (! isset($blockCache[$blockSlug])) {
                        $blockCache[$blockSlug] = \Backstage\Models\Block::where('slug', $blockSlug)
                            ->with('fields')
                            ->first();
                    }

                    $block = $blockCache[$blockSlug];

                    if (! $block || $block->fields->isEmpty()) {
                        continue;
                    }

                    $this->hydrateItemFields($item['data'], $block->fields);
                }
                unset($item);
            }
        }

        return $value;
    }

    private function tryHydrateViaClass(mixed $value, string $fieldType, ?Field $fieldModel = null): array
    {
        $fieldClass = \Backstage\Fields\Facades\Fields::resolveField($fieldType);

        if ($fieldClass) {
            if (in_array(\Backstage\Fields\Contracts\HydratesValues::class, class_implements($fieldClass))) {
                try {
                    $instance = app($fieldClass);
                    if ($fieldModel && property_exists($instance, 'field_model')) {
                        $instance->field_model = $fieldModel;
                    }

                    return [true, $instance->hydrate($value, $this)];
                } catch (\Throwable $e) {
                    file_put_contents('/tmp/hydration_error.log', "Hydration error for $fieldType: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);

                    return [true, $value];
                }
            } else {
                file_put_contents('/tmp/cfv_override_debug.log', "Class $fieldClass does not implement HydratesValues\n", FILE_APPEND);
            }
        } else {
            file_put_contents('/tmp/cfv_override_debug.log', "Could not resolve field class for $fieldType\n", FILE_APPEND);
        }

        return [false, null];
    }

    private function hydrateItemFields(array &$data, $fields): void
    {
        foreach ($fields as $child) {
            $key = null;
            if (array_key_exists($child->ulid, $data)) {
                $key = $child->ulid;
            } elseif (array_key_exists($child->slug, $data)) {
                $key = $child->slug;
            }

            if ($key) {
                if ($child->field_type === 'rich-editor') {
                    $html = self::getRichEditorHtml($data[$key] ?? '') ?? '';
                    $data[$key] = $this->shouldHydrate() ? new HtmlString($html) : $html;
                } else {
                    $data[$key] = $this->hydrateValuesRecursively($data[$key], $child);
                }
            }
        }
    }

    public function shouldHydrate(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        if (! request()) {
            return true;
        }

        $path = request()->path();

        // Broad check for admin/cms/livewire paths
        if (str($path)->contains(['admin', 'backstage', 'filament', 'livewire']) || request()->headers->has('X-Livewire-Id')) {
            return false;
        }

        // Check if there is a Filament panel active
        if (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::getCurrentPanel()) {
            return false;
        }

        return true;
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
        // Use getRawOriginal to bypass the accessor and prevent relationship hydration
        $rawValue = $this->getRawOriginal('value');
        $decoded = json_decode($rawValue, true);

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
