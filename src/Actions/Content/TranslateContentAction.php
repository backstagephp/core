<?php

namespace Backstage\Actions\Content;

use Backstage\Models\Content;
use Backstage\Resources\ContentResource\Pages\CreateContent;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Translations\Laravel\Facades\Translator;
use Filament\Actions\ReplicateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslateContentAction extends ReplicateAction
{
    public static function getNextAvailable(Model $model, string $field, string $value, string $languageCode): string
    {
        $baseName = preg_replace('/-\d+$/', '', $value);
        $copyNumber = 1;

        while (
            $model->where($field, $baseName . ($copyNumber > 1 ? '-' . $copyNumber : ''))
                ->where('language_code', $languageCode)
                ->exists()
        ) {
            $copyNumber++;
        }

        return $baseName;
    }

    protected function setUp(): void
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        parent::setUp();

        $this->label('Translate')
            ->after(function (Model $replica, array $arguments, Model $record): void {
                dispatch(function() use ($record, $arguments) {
                    if ($record->parent) {
                        $parent = static::translateContent(
                            record: $record->parent,
                            languageCode: $arguments['language']->code,
                        );
                    }

                    static::translateContent(
                        record: $record,
                        languageCode: $arguments['language']->code,
                        parent: $parent ?? null,
                    );
                });
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
            ->successRedirectUrl(fn (Model $replica): string => EditContent::getUrl(tenant: Filament::getTenant(), parameters: [
                'record' => $replica,
            ]));
    }

    public static function translateContent(Model $record, string $languageCode, Model $parent = null): Model
    {
        $content = Content::where('slug', $record->slug)->where('language_code', $languageCode)->first();

        if(! $content) {
            $content = $record->replicate();
            $content->save();
        }

        $content->parent_ulid = $parent?->getKey();

        $content->meta_tags = collect($content->meta_tags)
            ->mapWithKeys(function ($value, $key) use ($languageCode) {
                if (is_array($value) || is_null($value)) {
                    return [$key => $value];
                }

                return [
                    $key => Translator::translate($value, $languageCode),
                ];
            })
            ->toArray();

        $content->tags()->sync($record->tags->pluck('ulid')->toArray());

        $content->update([
            'name' => Translator::translate($record->name, $languageCode),
            'path' => static::getNextAvailable($content, 'path', Str::slug(Translator::translate($record->path, $languageCode)), $languageCode),
            'language_code' => $languageCode,
            'meta_tags' => $content->meta_tags,
        ]);

        foreach ($record->values as $originalValue) {
            if(count($originalValue->field->config['relations'] ?? []) !== 0) {
                continue;
            }

            $value = $originalValue->replicate();
            $value->content()->associate($content);
            $value->save();
        }

        $content = $content->load('values');

        $contentForm = new EditContent;
        $contentForm->boot();
        $contentForm->mount($content->getKey());
        $contentForm->form->fill(Translator::translate($contentForm->data, $languageCode));
        $contentForm->save();

        $content->edited_at = now();
        $content->save();

        return $content;
    }
}
