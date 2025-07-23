<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Actions\Content\DuplicateContentAction;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Models\Language;
use Backstage\Models\Tag;
use Backstage\Resources\ContentResource;
use Backstage\Translations\Laravel\Facades\Translator;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EditContent extends EditRecord
{
    use CanMapDynamicFields;

    protected static string $resource = ContentResource::class;

    public function getBreadcrumbs(): array
    {
        $originalBreadcrumbs = parent::getBreadcrumbs();

        if (! $this->getRecord()->parent) {
            return $originalBreadcrumbs;
        }

        $breadcrumbs = [];

        $first = true;
        foreach ($originalBreadcrumbs as $key => $breadcrumb) {
            $breadcrumbs[$key] = $breadcrumb;

            if ($first) {
                $parents = $this->getRecord()->ancestors()->get()->reverse();
                foreach ($parents as $parent) {
                    $parentUrl = route('filament.backstage.resources.content.edit', [
                        'record' => $parent,
                        'tenant' => Filament::getTenant(),
                    ]);
                    $breadcrumbs[$parentUrl] = $parent->name;
                }
            }

            $first = false;
        }

        return $breadcrumbs;

    }

    protected function getHeaderActions(): array
    {
        return [
            DuplicateContentAction::make('duplicate'),
            Actions\ActionGroup::make(
                Language::active()->pluck('code')->map(fn ($languageCode) => explode('-', $languageCode)[1] ?? '')->unique()->count() > 1 ?
                    // multiple countries
                    Language::active()->orderBy('name')
                        ->get()
                        ->groupBy(function ($language) {
                            return Str::contains($language->code, '-') ? strtolower(explode('-', $language->code)[1]) : '';
                        })->map(function ($languages, $countryCode) {
                            return Actions\ActionGroup::make(
                                $languages->map(function ($language) use ($countryCode) {
                                    return Actions\Action::make($language->code . '-' . $countryCode)
                                        ->label($language->name)
                                        ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg')))))
                                        ->action(fn () => $this->translate($language));
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
                    Language::active()->orderBy('name')->get()->map(function (Language $language) {
                        return Actions\Action::make($language->code)
                            ->label($language->name)
                            ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg')))))
                            ->action(fn () => $this->translate($language));
                    })->toArray()
            )
                ->label('Translate')
                ->icon('heroicon-o-language')
                ->iconPosition(IconPosition::Before)
                ->color('gray')
                ->button()
                ->visible(fn () => Language::active()->count() > 1),
            Actions\Action::make('Preview')
                ->color('gray')
                ->icon('heroicon-o-eye')
                ->url(fn () => $this->getRecord()->url)
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    public function translate($language)
    {
        $state = $this->form->getState();

        $values = collect($state['values'])->map(function ($value, $key) use ($language) {
            if (is_array($value)) {
                return collect($value)->map(function ($item) use ($language) {
                    if (is_array($item)) {
                        return collect($item)->map(function ($i) use ($language) {
                            if (isset($i['data'])) {
                                $i['data'] = collect($i['data'])->mapWithKeys(function ($text, $key) use ($language) {
                                    return [$key => Translator::translate($text, $language->code)];
                                })->toArray();
                            }

                            if (is_array($i)) {
                                return collect($i)->mapWithKeys(function ($value, $key) use ($language) {
                                    if (is_array($value) || is_null($value)) {
                                        return [$key => $value];
                                    }

                                    return [$key => Translator::translate($value, $language->code)];
                                })->toArray();
                            }

                            return $i;
                        })->toArray();
                    }

                    return $item;
                })->toArray();
            }

            if (is_null($value)) {
                return $value;
            }

            return Translator::translate($value, $language->code);
        })->toArray();

        $metaTags = collect($state['meta_tags'])->mapWithKeys(function ($value, $key) use ($language) {
            if (is_array($value) || is_null($value)) {
                return [$key => $value];
            }

            return [$key => Translator::translate($value, $language->code)];
        })->toArray();

        $state['values'] = $values;
        $state['meta_tags'] = $metaTags;
        $state['name'] = Translator::translate($state['name'], $language->code);

        $this->form->fill($state);

        Notification::make()
            ->title(__('Translated'))
            ->body(__('The content has been translated to ' . $language->name))
            ->send();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! isset($data[$this->getRecord()->valueColumn])) {
            $data[$this->getRecord()->valueColumn] = [];
        }

        // Get all values as an array: [ulid => value]
        $values = $this->getRecord()->values()->get()->mapWithKeys(function ($value) {
            if (! $value->field) {
                return [];
            }
            $value->value = json_decode($value->value, true) ?? $value->value;

            return [$value->field->ulid => $value->value];
        })->toArray();

        $this->getRecord()->values = $values;

        return $this->mutateBeforeFill($data);
    }

    protected function afterSave(): void
    {
        $this->handleTags();
        $this->handleValues();
        $this->updateEditedAt();
        $this->syncAuthors();
    }

    private function handleTags(): void
    {
        $tags = collect($this->data['tags'] ?? [])
            ->filter(fn ($tag) => filled($tag))
            ->map(fn (string $tag) => $this->record->tags()->updateOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]))
            ->each(fn (Tag $tag) => $tag->sites()->syncWithoutDetaching($this->record->site));

        $this->record->tags()->sync($tags->pluck('ulid')->toArray());
    }

    private function handleValues(): void
    {
        collect($this->data['values'] ?? [])
            ->each(function ($value, $field) {
                $fieldModel = \Backstage\Fields\Models\Field::where('ulid', $field)->first();

                $value = $this->prepareValue($value);

                if ($this->shouldDeleteValue($value)) {
                    $this->deleteValue($field);
                    return;
                }

                if ($fieldModel && $fieldModel->field_type === 'rich-editor') {
                    $value = $this->handleRichEditorField($value, $fieldModel);
                }

                if ($fieldModel && $fieldModel->field_type === 'builder') {
                    $this->handleBuilderField($value, $field);
                    return;
                }

                $this->updateOrCreateValue($value, $field);
            });
    }

    private function prepareValue($value)
    {
        return isset($value['value']) && is_array($value['value']) ? json_encode($value['value']) : $value;
    }

    private function shouldDeleteValue($value): bool
    {
        return blank($value);
    }

    private function deleteValue($field): void
    {
        $this->getRecord()->values()->where([
            'content_ulid' => $this->getRecord()->getKey(),
            'field_ulid' => $field,
        ])->delete();
    }

    private function handleRichEditorField($value, $fieldModel)
    {
        $autoCleanContent = $fieldModel->config['autoCleanContent'] ?? true;

        if ($autoCleanContent && !empty($value)) {
            $options = [
                'preserveCustomCaptions' => $fieldModel->config['preserveCustomCaptions'] ?? false,
            ];
            $value = \Backstage\Fields\Services\ContentCleaningService::cleanHtmlContent($value, $options);
        }

        return $value;
    }

    private function handleBuilderField($value, $field): void
    {
        $value = $this->decodeAllJsonStrings($value);

        $this->getRecord()->values()->updateOrCreate([
            'content_ulid' => $this->getRecord()->getKey(),
            'field_ulid' => $field,
        ], [
            'value' => is_array($value) ? json_encode($value) : $value,
        ]);
    }

    private function updateOrCreateValue($value, $field): void
    {
        $this->getRecord()->values()->updateOrCreate([
            'content_ulid' => $this->getRecord()->getKey(),
            'field_ulid' => $field,
        ], [
            'value' => is_array($value) ? json_encode($value) : $value,
        ]);
    }

    private function updateEditedAt(): void
    {
        $this->getRecord()->update([
            'edited_at' => now(),
        ]);
    }

    private function syncAuthors(): void
    {
        $this->getRecord()->authors()->syncWithoutDetaching(Auth::id());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->mutateBeforeSave($data);

        unset($data['tags']);
        unset($data['values']);

        return $data;
    }

    private function decodeAllJsonStrings($data, $path = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $currentPath = $path === '' ? $key : $path . '.' . $key;
                if (is_string($value)) {
                    $decoded = $value;
                    $decodeCount = 0;
                    while (is_string($decoded)) {
                        $json = json_decode($decoded, true);
                        if ($json !== null && (is_array($json) || is_object($json))) {
                            $decoded = $json;
                            $decodeCount++;
                        } else {
                            break;
                        }
                    }
                    if ($decodeCount > 0) {
                        $data[$key] = $this->decodeAllJsonStrings($decoded, $currentPath);
                    }
                } elseif (is_array($value)) {
                    $data[$key] = $this->decodeAllJsonStrings($value, $currentPath);
                }
            }
        }
        return $data;
    }
}
