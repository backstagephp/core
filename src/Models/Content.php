<?php

namespace Backstage\Models;

use Backstage\Casts\ContentPathCast;
use Backstage\Fields\Concerns\HasFields;
use Backstage\Fields\Models\Field;
use Backstage\Media\Concerns\HasMedia;
use Backstage\Models\Concerns\HasContentRelations;
use Backstage\Observers\ContentDepthObserver;
use Backstage\Observers\ContentObserver;
use Backstage\Shared\HasPackageFactory;
use Backstage\Shared\HasTags;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

/**
 * Backstage\Models\Content
 *
 * @property string $path
 * @property string|null $url
 * @property string $language_code
 * @property bool $public
 * @property string $view
 * @property string $type_slug
 */
#[ObservedBy(ContentDepthObserver::class)]
#[ObservedBy(ContentObserver::class)]
class Content extends Model
{
    use HasContentRelations;
    use HasFields;
    use HasMedia;
    use HasPackageFactory;
    use HasRecursiveRelationships;
    use HasTags;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'path' => ContentPathCast::class,
            'meta_tags' => 'array',
        ];
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'content_user', 'content_ulid', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ContentFieldValue::class)
            ->with('field');
    }

    /**
     * Get formatted field values as a key-value array where keys are field ULIDs.
     * This method processes JSON values and returns them in a format suitable for forms.
     * Used in the EditContent page to fill the form with existing values and
     * in the ContentResource to fill the table with existing values, which is needed in
     * the ManageChildrenContent page.
     */
    public function getFormattedFieldValues(): array
    {
        return $this->values()->get()->mapWithKeys(function ($value) {
            if (! $value->field) {
                return [];
            }
            $value->value = json_decode($value->value, true) ?? $value->value;

            return [$value->field->ulid => $value->value];
        })->toArray();
    }

    public function fields(): HasManyThrough
    {
        return $this->hasManyThrough(
            Field::class,
            Type::class,
            'slug',
            'model_key',
            'type_slug',
            'slug'
        )->where('model_type', 'type');
    }

    /**
     * @return Attribute<Provider, string>
     */
    protected function url(): Attribute
    {
        if (! $this->public) {
            return Attribute::make(
                get: fn () => null,
            );
        }

        $url = rtrim($this->pathPrefix . $this->path, '/');
        if ($this->site->trailing_slash) {
            $url .= '/';
        }

        return Attribute::make(
            get: fn () => $url,
        );
    }

    public function getParentKeyName()
    {
        return 'parent_ulid';
    }

    public function getLocalKeyName()
    {
        return 'ulid';
    }

    public function getPathName()
    {
        return 'parent_path';
    }

    /**
     * Custom depth column name.
     *
     * @see https://github.com/staudenmeir/laravel-adjacency-list/issues/87
     */
    public function getDepthName(): string
    {
        return 'backstage_depth';
    }

    /**
     * The full url, domain and language path. Without the content path, with trailing slash.
     *
     * @return Attribute<Provider, string>
     */
    protected function pathPrefix(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => self::getPathPrefixForLanguage($this->language_code, $this->site),
        );
    }

    /**
     * Calculate the path prefix for a given language code.
     * This is used in forms to update the path prefix live when language changes.
     */
    public static function getPathPrefixForLanguage(string $languageCode, ?Site $site = null): string
    {
        $url = '';

        if (! $site) {
            $site = Site::first();
        }

        if ($site) {
            $domain = $site->domains()->with([
                'languages' => function ($query) use ($languageCode) {
                    $query->where('code', $languageCode);
                    $query->limit(1);
                },
            ])
                ->where('environment', config('app.env'))
                ->first();

            if ($domain) {
                $url .= 'https://' . $domain->name;
                $url .= $site->path ? '/' . trim($site->path, '/') : '';
                if ($language = $domain->languages->first()) {
                    $url .= $language->pivot->path ? '/' . trim($language->pivot->path, '/') : '';
                }
            }
        }

        $url .= '/';

        return $url;
    }

    public function scopePublic($query): void
    {
        $query->where(function ($query) {
            return $query->where('public', true)
                ->where('published_at', '<=', now());
        });
    }

    public function blocks(string $field): array
    {
        return json_decode(
            json: $this->values->where('field.slug', $field)->first()?->value,
            associative: true
        ) ?? [];
    }

    /** Returns the value of a field based on the slug. */
    public function field(string $slug): HtmlString | Collection | array
    {
        $value = $this->values->where('field.slug', $slug)->first();

        if (! $value) {
            return new HtmlString('');
        }

        return $value->value();
    }

    public function rawField(string $field): mixed
    {
        return $this->values->where('field.slug', $field)->first()?->value;
    }

    public function view($data = [])
    {
        return View::first([$this->view, 'types.' . $this->type_slug, 'types.default', 'backstage::types.default'], $data);
    }

    public function response(int $code = 200)
    {
        view()->share([
            'content' => $this,
        ]);

        return response($this->view(), $code);
    }
}
