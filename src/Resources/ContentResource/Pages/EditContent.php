<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Actions\Content\DuplicateContentAction;
use Backstage\Actions\Content\TranslateContentAction;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Models\Content;
use Backstage\Models\Language;
use Backstage\Models\Tag;
use Backstage\Resources\ContentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EditContent extends EditRecord
{
    use CanMapDynamicFields;

    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DuplicateContentAction::make('duplicate'),
            Actions\ActionGroup::make(
                [
                    ...Language::active()->pluck('code')->map(fn($languageCode) => explode('-', $languageCode)[1] ?? '')->unique()->count() > 1 ?
                        // multiple countries
                        Language::active()->orderBy('name')
                        ->get()
                        ->filter(fn($language) => $this->record->language_code !== $language->code)
                        ->groupBy(function ($language) {
                            return Str::contains($language->code, '-') ? strtolower(explode('-', $language->code)[1]) : '';
                        })->map(function ($languages, $countryCode) {
                            return Actions\ActionGroup::make(
                                $languages->map(function ($language) use ($countryCode) {
                                    return TranslateContentAction::make('translate-' . $language->code . '-' . $countryCode)
                                        ->label($language->name)
                                        ->groupedIcon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg')))))
                                        ->arguments(['language' => $language]);
                                })
                                    ->toArray()
                            )
                                ->label(localized_country_name($countryCode) ?: __('Worldwide'))
                                ->color('gray')
                                ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . ($countryCode ?: 'worldwide') . '.svg')))))
                                ->iconPosition(IconPosition::After)
                                ->grouped();
                        })->toArray() :
                        // one country
                        Language::active()
                        ->orderBy('name')
                        ->get()
                        ->filter(fn($language) => $this->record->language_code !== $language->code)
                        ->map(function (Language $language) {
                            return TranslateContentAction::make('translate-' . $language->code)
                                ->label($language->name)
                                ->groupedIcon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg')))))
                                ->arguments(['language' => $language]);
                        })->toArray(),

                    ...collect(range(0, 1))
                        ->map(function (bool $usesSyc) {
                            return Actions\Action::make('reTranslate' . ($usesSyc ? 'Sync' : 'Queue'))
                                ->label(fn() => $usesSyc ? __('Re-translate (direct)') : __('Re-translate (background)'))
                                ->tooltip(fn(Actions\Action $action) => !$usesSyc ? $action->getLabel() : null)
                                ->action(function (Content $record, array $arguments) use ($usesSyc) {
                                    $replicate = false;

                                    TranslateContentAction::setTranslateOnSync($usesSyc);

                                    TranslateContentAction::translate(
                                        record: $record,
                                        language: $record->language,
                                        replicate: $replicate
                                    );
                                })
                                ->after(function () use ($usesSyc) {
                                    if ($usesSyc) {
                                        Notification::make()
                                            ->title(__('Content re-translation started'))
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title(__('Content re-translation queued'))
                                            ->body(__('The content translation has been queued and will be processed in the background. Please do not edit any content while the translation is in progress.'))
                                            ->success()
                                            ->send();
                                    }
                                })
                                ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . $this->record->language_code . '.svg')))));
                        }),


                ]
            )
                ->label('Translate')
                ->icon('heroicon-o-language')
                ->iconPosition(IconPosition::Before)
                ->color('gray')
                ->button()
                ->visible(fn() => Language::active()->count() > 1),
            Actions\Action::make('Preview')
                ->color('gray')
                ->icon('heroicon-o-eye')
                ->url(fn() => $this->getRecord()->url)
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['values'] = $this->getRecord()->values()->get()->mapWithKeys(function ($value) {

            if (! $value->field) {
                return [];
            }

            $value->value = json_decode($value->value, true) ?? $value->value;

            return [$value->field->ulid => $value->value];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $tags = collect($this->data['tags'] ?? [])
            ->filter(fn($tag) => filled($tag))
            ->map(fn(string $tag) => $this->record->tags()->updateOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]))
            ->each(fn(Tag $tag) => $tag->sites()->syncWithoutDetaching($this->record->site));

        $this->record->tags()->sync($tags->pluck('ulid')->toArray());

        collect($this->data['values'] ?? [])
            ->each(function ($value, $field) {

                $value = isset($value['value']) && is_array($value['value']) ? json_encode($value['value']) : $value;

                if (blank($value)) {
                    $this->getRecord()->values()->where([
                        'content_ulid' => $this->getRecord()->getKey(),
                        'field_ulid' => $field,
                    ])->delete();

                    return;
                }

                $this->getRecord()->values()->updateOrCreate([
                    'content_ulid' => $this->getRecord()->getKey(),
                    'field_ulid' => $field,
                ], [
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            });

        $this->getRecord()->update([
            'edited_at' => now(),
        ]);

        $this->getRecord()->authors()->syncWithoutDetaching(auth()->id());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->mutateBeforeSave($data);

        unset($data['tags']);
        unset($data['values']);

        return $data;
    }

    public function callFillForm()
    {
        $this->fillForm();
    }
}
