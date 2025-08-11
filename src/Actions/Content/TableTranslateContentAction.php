<?php

namespace Backstage\Actions\Content;

use Backstage\Jobs\Content\TranslateContent;
use Backstage\Models\Content;
use Backstage\Models\Language;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;

class TableTranslateContentAction extends BulkAction
{
    public static function getDefaultName(): string
    {
        return 'translate-content';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (): string => 'Translate');

        $this->requiresConfirmation();

        $this->modalIcon(fn (): string => 'heroicon-o-language');

        $this->modalHeading(function (array $arguments) {
            $language = Language::query()
                ->where('code', $arguments['language'])
                ->first();

            return __('Translate selected content to :language', [
                'language' => $language
                    ->native,
            ]);
        });

        $this->modalDescription(fn (): string => __('Are you sure you want to translate this content?'));

        $this->modalSubmitActionLabel(fn (): string => __('Translate'));

        $this->action(function (Collection $records, array $arguments) {
            $language = Language::query()
                ->where('code', $arguments['language'])
                ->first();

            $records = $records->filter(function (Content $record) use ($language) {
                $slug = $record->slug;

                return ! Content::query()
                    ->where('slug', $slug)
                    ->where('language_code', $language->code)
                    ->exists();
            });

            $records->each(function (Content $record) use ($language) {
                $slug = $record->slug;

                $existing = Content::query()
                    ->where('slug', $slug)
                    ->where('language_code', $language->code)
                    ->exists();

                if ($existing) {
                    return;
                }

                TranslateContent::dispatch(
                    $record,
                    $language
                );
            });
        });
    }
}
