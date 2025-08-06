<?php

namespace Backstage\Jobs\Content;

use Backstage\Models\Content;
use Illuminate\Bus\Queueable;
use Backstage\Models\Language;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Queue\Middleware\WithoutOverlapping;
use Backstage\Actions\Content\TranslateContentAction;
use Backstage\Translations\Laravel\Contracts\TranslatesAttributes;
use Backstage\Translations\Laravel\Facades\Translator;


class TranslateContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Content $content, public Language $language) {}

    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->content->getAttribute('ulid'))
        ];
    }

    public function handle(): void
    {
        $duplicatedContent = $this->content->replicate();

        $duplicatedContent->source_content_ulid = $this->content->ulid;

        $duplicatedContent->language_code = $this->language->code;

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

            if ($attribute == 'path') {
                $duplicatedContent->{$attribute} = str($duplicatedContent->{$attribute})->slug()->toString();
            }
        }

        $metaTags = collect($this->content->meta_tags ?? [])
            ->mapWithKeys(function ($value, $key) {
                return [
                    $key => is_array($value) || is_null($value)
                        ? $value
                        : Translator::translate($value, $this->language->code),
                ];
            })
            ->toArray();

        $duplicatedContent->meta_tags = $metaTags;

        $duplicatedContent->save();

        $duplicatedContent->refresh();

        $this->content->values()->get()->each(function (TranslatesAttributes $value) use ($duplicatedContent) {
            $value->translateAttribute(
                'value',
                $duplicatedContent->language_code,
            );
        });
    }
}
