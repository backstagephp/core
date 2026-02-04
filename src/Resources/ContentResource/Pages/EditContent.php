<?php

namespace Backstage\Resources\ContentResource\Pages;

use BackedEnum;
use Backstage\Actions\Content\DuplicateContentAction;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Fields\Concerns\PersistsContentData;
use Backstage\Models\Content;
use Backstage\Models\Type;
use Backstage\Resources\ContentResource;
use Backstage\Resources\ContentResource\Actions\TranslateActionGroup;
use Backstage\Translations\Laravel\Facades\Translator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditContent extends EditRecord
{
    public int $formVersion = 0;

    use CanMapDynamicFields;
    use PersistsContentData;

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

            TranslateActionGroup::make(),

            Action::make('preview')
                ->icon(fn (): BackedEnum => Heroicon::OutlinedEye)
                ->url(fn () => $this->getRecord()->url)
                ->color('gray')
                ->openUrlInNewTab()
                ->disabled(fn () => ! $this->getRecord()->previewable())
                ->tooltip(fn () => $this->getRecord()->previewable() ? __('Preview content') : __('Content must be public, published and not expired to preview')),

            DeleteAction::make(),
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
        $values = $this->getRecord()->getFormattedFieldValues();

        $this->getRecord()->values = $values;

        // Backwards compatibility: if meta_tags.robots is not set, use type default
        if (! isset($data['meta_tags']['robots']) || empty($data['meta_tags']['robots'])) {
            $type = $this->getRecord()->type;
            if ($type && $type->default_meta_tags_robots) {
                $data['meta_tags']['robots'] = $type->default_meta_tags_robots;
            }
        }

        return $this->mutateBeforeFill($data);
    }

    protected function beforeSave(): void
    {
        $this->getRecord()->fill([
            'edited_at' => now(),
        ]);
    }

    protected function afterSave(): void
    {
        $this->handleTags();
        $this->handleValues();
        $this->syncAuthors();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->mutateBeforeSave($data);

        // Store values for later processing in afterSave
        $this->data['values'] = $data['values'] ?? [];

        // Remove fields that should not be saved to the content table
        unset($data['tags'], $data['values']);

        return $data;
    }
}
