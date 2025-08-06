<?php

namespace Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Backstage\Models\Tag;
use Illuminate\Support\Str;
use Backstage\Models\Language;
use Filament\Facades\Filament;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Backstage\Resources\ContentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Actions\Content\DuplicateContentAction;
use Backstage\Actions\Content\TranslateContentAction;
use Backstage\Fields\Models\Field;
use Backstage\Translations\Laravel\Contracts\TranslatesAttributes;

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
                                ->arguments(['language' => $language->code]);
                        })->toArray(),
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
        $values = $this->getRecord()->values()->get()->mapWithKeys(function (TranslatesAttributes $value) {
            if (! $value->field) {
                return [];
            }

            $translatedAttribute = $value->getTranslatedAttribute('value', $this->getRecord()->language_code);

            return [$value->field->ulid => json_decode($translatedAttribute, true) ?? $translatedAttribute];
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
            ->filter(fn($tag) => filled($tag))
            ->map(fn(string $tag) => $this->record->tags()->updateOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]))
            ->each(fn(Tag $tag) => $tag->sites()->syncWithoutDetaching($this->record->site));

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

        if ($autoCleanContent && ! empty($value)) {
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
        /**
         * @var TranslatesAttributes|CreateContentAction $value
         */
        $record = $this->getRecord();

        $existing = $record->values()
            ->where('content_ulid', $record->getKey())
            ->where('field_ulid', $field)
            ->first();

        if (! $existing) {
            $value = is_array($value) ? json_encode($value) : $value;

            $value = $record->values()->create([
                'content_ulid' => $record->getKey(),
                'field_ulid' => $field,
                'value' => $value,
            ]);
        }

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

        $this->data['values'] = $data['values'];

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
