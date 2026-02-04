<?php

namespace Backstage\Resources\ContentResource\Actions;

use BackedEnum;
use Backstage\Models\Content;
use Backstage\Models\Language;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Text;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class TranslateActionGroup extends ActionGroup
{
    public static function make(array $actions = []): static
    {
        return parent::make([
            Action::make('translate'),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('Translate'));

        $this->icon(fn (): BackedEnum => Heroicon::OutlinedLanguage);

        $this->color('gray');

        $this->button();

        $languages = Language::query()
            ->when($this->getRecord()->language_code ?? null, function ($query, $languageCode) {
                $query->where('languages.code', '!=', $languageCode);
            })
            ->get();

        $languageActions = $languages
            ->map(fn (Language $language) => Action::make($language->code)
                ->hidden(fn (Content $record) => $record->language_code === $language->code)
                ->label(function (Content $record) use ($language) {
                    $existingTranslation = $record->existingTranslation($language);

                    $schema = $this->getLivewire()->content;

                    $icon = Icon::make($existingTranslation ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedMinusCircle)
                        ->color($existingTranslation ? 'success' : 'gray');

                    $entry = TextEntry::make($language->name)
                        ->hiddenLabel()
                        ->default(fn (): Htmlable => $icon->toHtmlString())
                        ->alignEnd();

                    $flex = Flex::make([
                        Text::make($language->name),

                        $entry,
                    ])
                        ->columnSpanFull()
                        ->container($schema)
                        ->toHtmlString();

                    return new HtmlString($flex);
                })
                ->icon(fn () => 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(flag_path(explode('-', $language->code)[0]))))
                ->modal(fn (Content $record) => ! $record->existingTranslation($language))
                ->modalIcon(fn () => Heroicon::OutlinedLanguage)
                ->modalHeading(fn () => __('Translate to ' . $language->name))
                ->modalDescription(fn () => __('This will create a new translation of the content in ' . $language->name))
                ->modalWidth(Width::Medium)
                ->modalAlignment(Alignment::Center)
                ->modalFooterActionsAlignment(Alignment::Center)
                ->modalSubmitActionLabel(__('Translate'))
                ->modalCancelActionLabel(__('Cancel'))
                ->action(function (Content $record) use ($language) {
                    $existingTranslation = $record->existingTranslation($language);

                    if ($existingTranslation) {
                        $url = $this->getLivewire()::getUrl([
                            'record' => $existingTranslation,
                        ], tenant: Filament::getTenant());

                        $this->getLivewire()->redirect($url);

                        return;
                    }

                    $record->translate($language);

                    Notification::make()
                        ->title(__('Translating :title to :language', ['title' => $record->name, 'language' => $language->name]))
                        ->body(__('The content is being translated to ' . $language->name))
                        ->icon(fn () => Heroicon::OutlinedLanguage)
                        ->iconColor('success')
                        ->iconSize(IconSize::TwoExtraLarge)
                        ->send();
                }));

        if (! $languageActions->isEmpty()) {
            $all = $languageActions->all();

            $translateAllLanguage = Action::make('translate_all')
                ->label(__('Translate to all languages'))
                ->icon(fn () => Heroicon::OutlinedLanguage)
                ->requiresConfirmation()
                ->action(function (Content $record) use ($languages) {
                    foreach ($languages as $language) {
                        $record->translate(language: $language);
                    }
                });

            $languageActions = collect([$translateAllLanguage, ...$all]);
        }

        $this->actions([...$languageActions->toBase()]);
    }

    public function contentHasLocale(string $code, Content $record): bool
    {
        return $record->existingTranslation(Language::where('code', $code)->first()) !== null;
    }
}
