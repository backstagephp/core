<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Locale;
use Filament\Actions;
use Illuminate\Support\HtmlString;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Resources\ContentResource;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make(
                Language::distinct('country_code')->count() > 1 ?
                    // multiple countries
                    Language::orderBy('name')->get()->groupBy('country_code')->map(function ($languages, $countryCode) {
                        return Actions\ActionGroup::make(
                            $languages->map(function ($language) {
                                return Actions\Action::make($language->code . '-' . $language->countryCode)
                                    ->label($language->name)
                                    ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg')))))
                                    ->url('#');
                            })
                                ->toArray()
                        )
                            ->label(Locale::getDisplayRegion('-' . $countryCode, app()->getLocale()) ?: 'Worldwide')
                            ->color('gray')
                            ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $countryCode . '.svg')))))
                            ->iconPosition(IconPosition::After)
                            ->grouped();
                    })->toArray() :
                    // one country
                    Language::orderBy('name')->get()->map(function (Language $language) {
                        return Actions\Action::make($language->code . '-' . $language->countryCode)
                            ->label($language->name)
                            ->icon(new HtmlString('data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg')))))
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
                ->url(fn() => $this->getRecord()->url)
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['values'] = $this->getRecord()->values()->get()->mapWithKeys(function ($value) {
            if ($value->field->field_type == 'builder') {
                $value->value = json_decode($value->value, true);
            }

            return [$value->field->ulid => $value->value];
        })->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        collect($this->data['values'] ?? [])->each(function ($value, $field) {
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
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['values']);

        return $data;
    }
}
