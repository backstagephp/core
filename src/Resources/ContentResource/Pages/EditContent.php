<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Actions\ReplicateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Locale;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Tag;
use Vormkracht10\Backstage\Resources\ContentResource;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReplicateAction::make()
                ->label('Duplicate')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->beforeReplicaSaved(function (Model $replica): void {
                    $replica->edited_at = now();
                })
                ->modalHeading("Duplicate {$this->getRecord()->name} {$this->getRecord()->type->name}")
                ->requiresConfirmation()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Content duplicated')
                        ->body(fn () => "The content '" . $this->getRecord()->name . "' has been duplicated."),
                )
                ->successRedirectUrl(fn (Model $replica): string => route('filament.backstage.resources.content.edit', [
                    'tenant' => Filament::getTenant(),
                    'record' => $replica,
                ])),
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
                ->visible(fn () => Language::where('active', 1)->count() > 1),
            Actions\Action::make('Preview')
                ->color('gray')
                ->icon('heroicon-o-eye')
                ->url(fn () => $this->getRecord()->url)
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
        $tags = collect($this->data['tags'] ?? [])
            ->filter(fn ($tag) => filled($tag))
            ->map(fn (string $tag) => $this->record->tags()->updateOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]))
            ->each(fn (Tag $tag) => $tag->sites()->syncWithoutDetaching($this->record->site));

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
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['tags']);
        unset($data['values']);

        return $data;
    }
}
