<?php

namespace Backstage\Resources\ContentResource\Pages;

use Locale;
use Filament\Actions;
use Backstage\Models\Tag;
use Illuminate\Support\Str;
use Backstage\Translations\Laravel\Models\Language;
use Illuminate\Support\HtmlString;
use Backstage\Resources\ContentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Actions\Content\DuplicateContentAction;

class EditContent extends EditRecord
{
    use CanMapDynamicFields;

    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DuplicateContentAction::make('duplicate'),
            Actions\ActionGroup::make(
                Language::pluck('code')->map(fn($languageCode) => explode('-', $languageCode)[1] ?? '')->unique()->count() > 1 ?
                    // multiple countries
                    Language::orderBy('name')
                    ->get()
                    ->groupBy(function ($language) {
                        return Str::contains($language->code, '-') ? strtolower(explode('-', $language->code)[1]) : '';
                    })->map(function ($languages, $countryCode) {
                        return Actions\ActionGroup::make(
                            $languages->map(function ($language) use ($countryCode) {
                                return Actions\Action::make($language->code . '-' . $countryCode)
                                    ->label($language->name)
                                    ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg')))))
                                    ->url('#');
                            })
                                ->toArray()
                        )
                            ->label(Locale::getDisplayRegion('-' . $countryCode, app()->getLocale()) ?: 'Worldwide')
                            ->color('gray')
                            ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . ($countryCode ?: 'worldwide') . '.svg')))))
                            ->iconPosition(IconPosition::After)
                            ->grouped();
                    })->toArray() :
                    // one country
                    Language::orderBy('name')->get()->map(function (Language $language) {
                        return Actions\Action::make($language->code)
                            ->label($language->name)
                            ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg')))))
                            ->url('#');
                    })
                    ->toArray()
            )
                ->label('Translate')
                ->icon('heroicon-o-language')
                ->iconPosition(IconPosition::Before)
                ->color('gray')
                ->button()
                ->visible(fn() => Language::where('active', 1)->count() > 1),
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
}
