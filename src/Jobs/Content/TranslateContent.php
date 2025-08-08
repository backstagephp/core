<?php

namespace Backstage\Jobs\Content;

use Backstage\Models\Content;
use Illuminate\Bus\Queueable;
use Backstage\Models\Language;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Backstage\Models\ContentFieldValue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Backstage\Translations\Laravel\Facades\Translator;
use Backstage\Translations\Laravel\Domain\Translatables\Actions\TranslateAttribute;

class TranslateContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public $contentUlid = null;

    public function __construct(
        public Content $content,
        public Language $language
    ) {}

    public function handle(): void
    {
        try {
            if ($this->content->language_code === $this->language->code) {
                Log::info('Skipping translation: Content already in target language.', [
                    'content_ulid' => $this->content->ulid,
                    'language_code' => $this->language->code,
                ]);
                return;
            }

            $duplicatedContent = $this->content->replicate(['ulid']);
            $duplicatedContent->language_code = $this->language->code;
            $duplicatedContent->meta_tags = [];
            $duplicatedContent->edited_at = now();
            $duplicatedContent->save();

            if ($this->content->parent_ulid) {
                $parent = Content::where('ulid', $this->content->parent_ulid)->first();

                if ($parent) {
                    $parentTranslation = Content::where('ulid', $parent->ulid)
                        ->where('language_code', $this->language->code)
                        ->first();

                    if (!$parentTranslation) {
                        $newInstance = new self($parent, $this->language);

                        $newInstance->handle();
                        $parentTranslationUlid = $newInstance->contentUlid;
                    } else {
                        $parentTranslationUlid = $parentTranslation->ulid;
                    }

                    if ($parentTranslationUlid) {
                        $duplicatedContent->parent_ulid = $parentTranslationUlid;
                    }
                }
            }

            $translatableAttributes = ['name', 'path'];
            foreach ($translatableAttributes as $attribute) {
                if ($this->content->{$attribute}) {
                    $duplicatedContent->{$attribute} = Translator::translate(
                        $this->content->{$attribute},
                        $this->language->code
                    );
                }
            }

            if (!empty($this->content->meta_tags)) {
                $duplicatedContent->meta_tags = TranslateAttribute::translateArray(
                    model: null,
                    attribute: null,
                    data: $this->content->meta_tags,
                    targetLanguage: $this->language->code,
                    rules: ['!robots', 'title', 'description', 'keywords.*']
                );
            }

            $this->content->values()->get()->each(function (ContentFieldValue $value) use ($duplicatedContent) {
                $duplicatedValue = $value->replicate(['ulid']);
                $duplicatedValue->content_ulid = $duplicatedContent->ulid;

                if ($this->isJson($value->value)) {
                    $array = json_decode($value->value, true);
                    $translatedArray = TranslateAttribute::translateArray(
                        model: null,
                        attribute: null,
                        targetLanguage: $duplicatedContent->language_code,
                        data: $array,
                        rules: ['*data']
                    );
                    $duplicatedValue->value = json_encode($translatedArray, JSON_UNESCAPED_UNICODE);
                } elseif (!empty($value->value)) {
                    $duplicatedValue->value = Translator::translate(
                        $value->value,
                        $duplicatedContent->language_code
                    );
                }

                $duplicatedValue->save();
            });

            $duplicatedContent->save();
            $this->contentUlid = $duplicatedContent->ulid;
        } catch (\Exception $e) {
            Log::error('Failed to translate content.', [
                'content_ulid' => $this->content->ulid,
                'language_code' => $this->language->code,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    private function isJson($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->content->ulid . '-' . $this->language->code))
                ->expireAfter(1800),
        ];
    }
}
