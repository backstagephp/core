<?php

namespace Backstage\Actions\Content;

use Backstage\Models\Content;
use Filament\Facades\Filament;
use Filament\Actions\ReplicateAction;
use Backstage\Models\ContentFieldValue;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Backstage\Translations\Laravel\Facades\Translator;
use Backstage\Resources\ContentResource\Pages\EditContent;

class TranslateContentAction extends ReplicateAction
{
    protected function getNextAvailableName(Model $model, string $field, string $value, string $languageCode): string
    {
        $baseName = preg_replace('/-\d+$/', '', $value);
        $copyNumber = 2;

        while (
            $model->where($field, $baseName . '-' . $copyNumber)
            ->where('language_code', $languageCode)
            ->exists()
        ) {
            $copyNumber++;
        }

        return $baseName . '-' . ($copyNumber + 1);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Translate')
            ->beforeReplicaSaved(function (Model $replica, array $arguments): void {
                $language = $arguments['language'];

                $replica->edited_at = now();

                $replica->path = $this->getNextAvailableName($replica, 'path', $replica->path, $language->code);
                $replica->slug = $this->getNextAvailableName($replica, 'slug', $replica->slug, $language->code);
            })
            ->after(function (Model $replica, array $arguments, Model $record): void {
                if ($record->parent) {
                    $replicaParent = $record
                        ->parent
                        ->replicate();

                    static::handleAfterReplicaSaved(
                        replica: $replicaParent,
                        arguments: $arguments,
                        record: $replicaParent,
                    );

                    $replica->parent_ulid = $replicaParent
                        ->ulid;
                }

                static::handleAfterReplicaSaved(
                    replica: $replica,
                    arguments: $arguments,
                    record: $record,
                );
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

                return $notification->title(fn() => __('Content translated'))
                    ->body($body);
            })
            ->successRedirectUrl(fn(Model $replica): string => EditContent::getUrl(tenant: Filament::getTenant(), parameters: [
                'record' => $replica,
            ]))
        ;
    }

    public static function handleAfterReplicaSaved(Model $replica, array $arguments, Model $record): void
    {
        $language = $arguments['language'];

        $replica->meta_tags = collect($replica->meta_tags)
            ->mapWithKeys(function ($value, $key) use ($language) {
                if (is_array($value) || is_null($value)) {
                    return [$key => $value];
                }

                return [
                    $key => Translator::translate($value, $language->code)
                ];
            })
            ->toArray();

        $replica
            ->tags()
            ->sync($record->tags->pluck('ulid')
                ->toArray());

        $replica->update([
            'name' => Translator::translate($record->name, $language->code),
            'language_code' => $language->code,
            'meta_tags' => $replica->meta_tags,
        ]);

        $originalValues = $record->values;

        foreach ($originalValues as $originalValue) {
            /**
             * @var ContentFieldValue $value 
             */
            $value = $originalValue
                ->replicate();

            $value
                ->content()
                ->associate($replica);

            $value
                ->save();
        }

        /**
         * @var Content $replica
         */
        $replica = $replica
            ->load('values');

        /**
         * @var EditContent $editContent
         */
        $editContent = new EditContent;

        $editContent
            ->boot();

        $editContent
            ->mount($replica->ulid ?? $replica->getKey());

        $editContent
            ->form
            ->fill(Translator::translate($editContent->data, $language->code));

        $editContent
            ->save();
    }
}
