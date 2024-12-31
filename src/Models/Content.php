<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Vormkracht10\Backstage\Casts\ContentPathCast;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Vormkracht10\Backstage\Shared\HasTags;
use Vormkracht10\MediaPicker\Concerns\HasMedia;

/**
 * Vormkracht10\Backstage\Models\Content
 *
 * @property string $path
 * @property string $url
 * @property string $language_code
 */
class Content extends Model
{
    use HasMedia;
    use HasPackageFactory;
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
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<Provider, string>
     */
    protected function url(): Attribute
    {
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
            }])
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

    public function field(string $field): HtmlString
    {
        return new HtmlString($this->values->where('field.slug', $field)->first()?->value);
    }

    public function rawField(string $field): mixed
    {
        return $this->values->where('field.slug', $field)->first()?->value;
    }

    public function view($data = [])
    {
        return View::first([$this->template_file, 'types.page', 'backstage::types.page'], $data);
    }

    public function response(int $code = 200)
    {
        view()->share([
            'content' => $this,
        ]);

        return response($this->view(), $code);
    }
}
