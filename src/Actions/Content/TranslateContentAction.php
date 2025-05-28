<?php

namespace Backstage\Actions\Content;

use Backstage\Models\Content;
use Backstage\Models\Language;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Translations\Laravel\Facades\Translator;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslateContentAction extends Action
{
    protected static $translatedContent = null;

    public static function getNextAvailable(Model $model, string $field, string $value, Language $language): string
    {
        $baseName = preg_replace('/-\d+$/', '', $value);
        $copyNumber = 1;

        while (
            $model->where($field, $baseName . ($copyNumber > 1 ? '-' . $copyNumber : ''))
                ->where('language_code', $language->code)
                ->exists()
        ) {
            $copyNumber++;
        }

        return $baseName;
    }

    protected function setUp(): void
    {
        // @todo: for testing in sync only
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        parent::setUp();

        $this->label('Translate')
            ->action(function (Model $record, array $arguments): void {
                static::$translatedContent = static::translate($record, $arguments['language'], sync: true);
            })
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-language')
            ->modalHeading(function () {
                $language = $this->getArguments()['language'];

                return __('Translate :name (:type) to :language', [
                    'name' => $this->getRecord()
                        ?->name,
                    'type' => $this->getRecord()
                        ?->type
                        ?->name,
                    'language' => $language
                        ->native,
                ]);
            })
            ->modalDescription(__('Are you sure you want to translate this content?'))
            ->modalSubmitActionLabel(__('Translate'))
            ->successNotification(function (Notification $notification) {
                $record = $this->getRecord();

                $body = __("The content ':name' has been translated.", [
                    'name' => $record ? $record->name : '',
                ]);

                return $notification->title(fn () => __('Content translated'))
                    ->body($body);
            })
            ->successRedirectUrl(fn (): string => EditContent::getUrl(tenant: Filament::getTenant(), parameters: [
                'record' => static::$translatedContent,
            ]));
    }

    public static function translate(Model $record, Language $language, bool $sync = false)
    {
        if($sync) {
            return static::translateParentAndContent($record, $language);
        }

        dispatch(fn()  => static::translateParentAndContent($record, $language));
    }

    public static function translateParentAndContent(Model $record, Language $language)
    {
        $record->load('parent');

        if ($record->parent) {
            $parent = static::translateContent(
                record: $record->parent,
                language: $language,
            );
        }

        $content = static::translateContent(
            record: $record,
            language: $language,
            parent: $parent ?? null,
        );

        return $content;
    }

    public static function translateContent(Model $record, Language $language, ?Model $parent = null): Model
    {
        $content = Content::where('slug', $record->slug)->where('language_code', $language->code)->first();

        if(! $content) {
            $content = $record->replicate();
            $content->save();
        }

        $content->update([
            'language_code' => $language->code,
        ]);

        $content->meta_tags = collect($content->meta_tags)
            ->mapWithKeys(function ($value, $key) use ($language) {
                if (is_array($value) || is_null($value)) {
                    return [$key => $value];
                }

                return [
                    $key => Translator::translate($value, $language->code),
                ];
            })
            ->toArray();

        $content->update([
            'name' => Translator::translate($record->name, $language->code),
            'path' => static::getNextAvailable($content, 'path', Str::slug(Translator::translate($record->path, $language->code)), $language),
            'meta_tags' => $content->meta_tags,
        ]);

        $content->tags()->sync($record->tags->pluck('ulid')->toArray());

        foreach ($record->values as $originalValue) {
            $value = $originalValue->replicate();
            $value->content()->associate($content);
            $value->save();
        }

        $content->parent_ulid = $parent?->ulid;
        $content->edited_at = now();
        $content->save();

        $content->refresh();

        $content->load('values', 'parent');

        $contentForm = new EditContent;
        $contentForm->boot();
        $contentForm->mount($content->getKey());
        $contentForm->form->fill(Translator::translate($contentForm->data, $language->code));
        $contentForm->save();

        return $content;
    }
}
