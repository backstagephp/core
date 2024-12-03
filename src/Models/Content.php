<?php

namespace Vormkracht10\Backstage\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\HtmlString;
use Vormkracht10\Backstage\Shared\HasPackageFactory;
use Vormkracht10\Backstage\Shared\HasTags;

class Content extends Model
{
    use HasPackageFactory;
    use HasTags;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'meta_tags' => 'array',
        ];
    }

    public function fields(): MorphMany
    {
        return $this->morphMany(Field::class, 'model', 'model_type', 'model_key', 'slug');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ContentFieldValue::class)
            ->with('field');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, ['code', 'country_code']);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => url(ltrim($attributes['path'], '/')),
        );
    }

    protected function templateFile(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => $attributes['template_slug'],
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
        $view = view($this->templateFile ?? 'backstage::types.page');

        if (filled($data)) {
            $view->with($data);
        }

        return $view;
    }

    public function response(int $code = 200)
    {
        view()->share([
            'content' => $this,
        ]);

        return response($this->view(), $code);
    }
}
