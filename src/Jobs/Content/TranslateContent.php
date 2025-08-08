<?php

namespace Backstage\Jobs\Content;

use Backstage\Models\Content;
use Illuminate\Bus\Queueable;
use Backstage\Models\Language;
use Illuminate\Queue\SerializesModels;
use Backstage\Models\ContentFieldValue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Backstage\Actions\Content\TranslateContentAction;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Translations\Laravel\Facades\Translator;
use Backstage\Translations\Laravel\Contracts\TranslatesAttributes;
use Backstage\Translations\Laravel\Domain\Translatables\Actions\TranslateAttribute;
use PDO;

class TranslateContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $timeout = 3600;

    public function __construct(public Content $content, public Language $language) {}

    public function handle(): void
    {
        $duplicatedContent = $this->content->replicate();

        $duplicatedContent->language_code = $this->language->code;

        $this->content->load('parent');

        $duplicatedContent->parent_ulid = $this->content->parent?->ulid ?? null;

        $duplicatedContent->edited_at = now();

        $translatableAttributes = [
            'name',
            'path',
        ];

        foreach ($translatableAttributes as $attribute) {
            $duplicatedContent->{$attribute} = Translator::translate(
                $this->content->{$attribute},
                $this->language->code
            );
        }

        $metaTags = TranslateAttribute::translateArray(
            model: null,
            attribute: null,
            data: $this->content->meta_tags,
            targetLanguage: $this->language->code,
            rules: [
                '!robots',
                'title',
                'description',
                'keywords.*',
            ]
        );

        $duplicatedContent->meta_tags = $metaTags;
        $duplicatedContent->save();

        $duplicatedContent->refresh();

        $this->content
            ->values()
            ->get()
            ->map(function (ContentFieldValue $value) use ($duplicatedContent) {
                $duplicatedValue = $value->replicate();

                $duplicatedValue->content_ulid = $duplicatedContent->getKey();

                if (is_array($duplicatedValue->value) || json_validate($duplicatedValue->value)) {
                    $value = is_string($duplicatedValue->value) ? json_decode($duplicatedValue->value, true) : $duplicatedValue->value;

                    $array = TranslateAttribute::translateArray(
                        model: null,
                        attribute: null,
                        targetLanguage: $duplicatedContent->language_code,
                        data: $value,
                        rules: [
                            '*data'
                        ]
                    );

                    $duplicatedValue->value = json_encode($array);
                } else {
                    $duplicatedValue->value = Translator::translate(
                        $duplicatedValue->value,
                        $duplicatedContent->language_code
                    );
                }

                $duplicatedValue->save();

                return $duplicatedValue;
            });
    }
}
