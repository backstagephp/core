<?php

namespace Backstage\Actions\Content;

use Filament\Forms\Form;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Backstage\Models\Content;
use Filament\Facades\Filament;
use Filament\Actions\ReplicateAction;
use Backstage\Models\ContentFieldValue;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Backstage\Translations\Laravel\Facades\Translator;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Filament\Pages\Page;

class TranslateContentAction extends ReplicateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Translate')
            ->beforeReplicaSaved(function (Model $replica): void {
                $replica->edited_at = now();
            })
            ->after(function (Model $replica, array $arguments): void {
                static::afterReplicate($replica, $this->getRecord(), $arguments);
            })
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-language')
            ->modalHeading(function () {
                $language = $this->getArguments()['language'];

                return __('Translate :name (:type) to :language', [
                    'name' => $this->getRecord()?->name,
                    'type' => $this->getRecord()?->type?->name,
                    'language' => $language->native,
                ]);
            })
            ->modalDescription(__('Are you sure you want to translate this content?'))
            ->modalSubmitActionLabel(__('Translate'))
            ->successNotification(
                fn(Notification $notification) => $notification->title('Content translated')
                    ->body(fn() => "The content '" . $this->getRecord()->name . "' has been translated.")
            )
            ->successRedirectUrl(fn(Model $replica): string => EditContent::getUrl(tenant: Filament::getTenant(), parameters: [
                'record' => $replica,
            ]))
        ;
    }

    public static function afterReplicate(Model $replicate, Model $original, array $arguments): void
    {
        $language = $arguments['language'];

        $replicate->meta_tags = collect($replicate->meta_tags)->mapWithKeys(function ($value, $key) use ($language) {
            if (is_array($value) || is_null($value)) {
                return [$key => $value];
            }

            return [$key => Translator::translate($value, $language->code)];
        })->toArray();

        $replicate->tags()->sync($original->tags->pluck('ulid')->toArray());

        $replicate->update([
            'name' => $name = Translator::translate($original->name, $language->code),
            'path' => Str::slug($name),
            'language_code' => $language->code,
            'meta_tags' => $replicate->meta_tags,
        ]);

        $originalValues = $original->values;

        foreach ($originalValues as $originalValue) {
            /**
             * @var ContentFieldValue $value 
             */
            $value = $originalValue->replicate();

            $value->content()->associate($replicate);

            $value->save();
        }

        /**
         * @var Content $replica
         */
        $replica = $replicate->load('values');

        /**
         * @var EditContent $editContent
         */
        $editContent = new EditContent;

        $editContent->boot();

        $editContent->mount($replica->ulid);

        $jsonData = json_encode($editContent->data, JSON_THROW_ON_ERROR);

        $translatedJson = Translator::translate($jsonData, $language->code);

        $editContent->data = json_decode($translatedJson, associative: true);

        $editContent->save();
    }
}
