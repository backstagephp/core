<?php

namespace Backstage\Models;

use Backstage\Casts\ContentPathCast;
use Backstage\Fields\Models\Field;
use Backstage\Media\Concerns\HasMedia;
use Backstage\Shared\HasPackageFactory;
use Backstage\Shared\HasTags;
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
 * @property string $type_slug
 */
class Content extends Model
{
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
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Provider, string>
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

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Provider, string>
     */
    protected function templateFile(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['template_slug'],
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
     * The full url, domain and language path. Without the content path, with trailing slash.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Provider, string>
     */
    protected function pathPrefix(): Attribute
    {
        $url = '';

        $domain = $this->site?->domains()->with([
            'languages' => function ($query) {
                $query->where('code', $this->language_code);
                $query->limit(1);
            },
        ])
            ->first();

        if ($domain) {
            $url .= 'https://' . $domain->name;
            $url .= $this->site->path ? '/' . trim($this->site->path, '/') : '';
            if ($language = $domain->languages->first()) {
                $url .= $language->pivot->path ? '/' . trim($language->pivot->path, '/') : '';
            }
        }

        $url .= '/';

        return Attribute::make(
            get: fn (?string $value, array $attributes) => $url,
        );
    }

    public function blocks(string $field): array
    {
        return json_decode(
            json: $this->values->where('field.slug', $field)->first()?->value,
            associative: true
        ) ?? [];
    }

    /**
     * Returns the value of a field based on the slug.
     *
     * @return HtmlString|Collection
     */
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
        return View::first([$this->template_file, 'types.' . $this->type_slug, 'types.default', 'backstage::types.default'], $data);
    }

    public function response(int $code = 200)
    {
        view()->share([
            'content' => $this,
        ]);

        return response($this->view(), $code);
    }
}
