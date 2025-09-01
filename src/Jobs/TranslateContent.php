<?php

namespace Backstage\Jobs;

use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Backstage\Models\Language;
use Backstage\Translations\Laravel\Domain\Translatables\Actions\TranslateAttribute;
use Backstage\Translations\Laravel\Facades\Translator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class TranslateContent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 3600;

    public $contentUlid = null;

    public function __construct(
        public Content $content,
        public Language $language
    ) {}

    public function handle(): void
    {

        if ($this->content->language_code === $this->language->code || Content::query()->where('slug', $this->content->slug)->where('language_code', $this->language->code)->exists()) {
            return;
        }

        $duplicatedContent = $this->content->replicate(['ulid']);
        $duplicatedContent->language_code = $this->language->code;
        $duplicatedContent->meta_tags = [];
        $duplicatedContent->edited_at = now();
        $duplicatedContent->save();

        $parentTranslationUlid = null;

        if ($this->content->parent_ulid) {
            $parent = Content::where('ulid', $this->content->parent_ulid)->first();

            if ($parent) {
                $parentTranslation = Content::where('slug', $parent->slug)
                    ->where('language_code', $this->language->code)
                    ->first();

                if (! $parentTranslation) {
                    $newInstance = new self($parent, $this->language);

                    $newInstance->handle();

                    $parentTranslationUlid = $newInstance->contentUlid;
                } else {
                    $parentTranslationUlid = $parentTranslation->ulid;
                }
            }
        }

        if ($parentTranslationUlid) {
            $duplicatedContent->parent_ulid = $parentTranslationUlid;
        }

        // Translate name and path
        if ($this->content->name) {
            $duplicatedContent->name = Translator::translate(
                $this->content->name,
                $this->language->code,
                $this->getExtraPrompt()
            );
        }
        if ($this->content->path) {
            // Get ancestors paths
            $fullPath = '';
            foreach ($duplicatedContent->ancestors as $ancestor) {
                if ($ancestor->language_code === $this->language->code) {
                    $fullPath .= $ancestor->path . '/';
                }
            }

            $path = explode('/', $this->content->path);
            $contentPath = end($path);
            $translatedPath = Translator::translate(
                $contentPath,
                $this->language->code,
                $this->getExtraPrompt()

            );
            $duplicatedContent->path = rtrim($fullPath . $translatedPath, '/');
        }

        if (! empty($this->content->meta_tags)) {
            $duplicatedContent->meta_tags = TranslateAttribute::translateArray(
                model: null,
                attribute: null,
                data: $this->content->meta_tags,
                targetLanguage: $this->language->code,
                rules: ['!robots', 'title', 'description', 'keywords.*'],
                extraPrompt: $this->getExtraPrompt()
            );
        }

        $this->content->values()->get()->each(function (ContentFieldValue $value) use ($duplicatedContent) {
            $duplicatedValue = $value->replicate(['ulid']);
            $duplicatedValue->content_ulid = $duplicatedContent->ulid;

            if ($this->isJson($value->value)) {
                $array = json_decode($value->value, true);

                if (! is_int($array)) {
                    $translatedArray = TranslateAttribute::translateArray(
                        model: null,
                        attribute: null,
                        targetLanguage: $duplicatedContent->language_code,
                        data: $array,
                        rules: ['*data'],
                        extraPrompt: $this->getExtraPrompt()
                    );
                    $duplicatedValue->value = json_encode($translatedArray, JSON_UNESCAPED_UNICODE);
                } else {
                    $duplicatedValue->value = Translator::translate(
                        $value->value,
                        $duplicatedContent->language_code,
                        $this->getExtraPrompt()
                    );
                }
            } elseif (! empty($value->value)) {
                $duplicatedValue->value = Translator::translate(
                    $value->value,
                    $duplicatedContent->language_code,
                    $this->getExtraPrompt()
                );
            }

            $duplicatedValue->save();
        });

        $duplicatedContent->save();

        $this->contentUlid = $duplicatedContent->ulid;
    }

    protected function isJson($value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function getExtraPrompt()
    {
        return isset($this->content->type->name) ? 'This translation is in the context of ' . $this->content->type->name : '';
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->content->ulid . '-' . $this->language->code))
                ->expireAfter(1800),
        ];
    }
}
