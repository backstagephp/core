<?php

namespace Backstage\Actions\Content;

use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Translations\Laravel\Facades\Translator;
use Filament\Actions\ReplicateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslateContentAction extends ReplicateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Translate')
            ->beforeReplicaSaved(function (Model $replica): void {
                $replica->edited_at = now();
            })
            ->after(function (Model $replica): void {
                $language = $this->getArguments()['language'];

                $replica->meta_tags = collect($replica->meta_tags)->mapWithKeys(function ($value, $key) use ($language) {
                    if (is_array($value) || is_null($value)) {
                        return [$key => $value];
                    }

                    return [$key => Translator::translate($value, $language->code)];
                })->toArray();

                $replica->update([
                    'name' => $name = Translator::translate($this->getRecord()->name, $language->code),
                    'path' => Str::slug($name),
                    'language_code' => $language->code,
                    'meta_tags' => $replica->meta_tags,
                ]);

                $values = $this->getRecord()->values->map(function ($value, $key) use ($language) {
                    if(null !== $v = json_decode($value->value, associative: true)) {
                        foreach($v as $key => $vv) {
                            $v['data'][$key] = Translator::translate($vv, $language->code);
                        }

                        $value->value = json_encode($v);

                        return $value;
                    }

                    $value->value = Translator::translate($value->value, $language->code);

                    return $value;
                });

                $replica->values()->delete();

                if(count($values ?? [])) {
                    $replica->values()->createMany($values->map(fn ($value) => ['value' => $value]));
                }

                $replica->tags()->sync($this->getRecord()->tags->pluck('ulid')->toArray());
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
                fn (Notification $notification) => $notification->title('Content translated')
                    ->body(fn () => "The content '" . $this->getRecord()->name . "' has been translated.")
            )
            ->successRedirectUrl(fn (Model $replica): string => EditContent::getUrl(tenant: Filament::getTenant(), parameters: [
                'record' => $replica,
            ]));
    }
}
