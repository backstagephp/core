<?php

namespace Backstage\Actions\Content;

use Illuminate\Support\Str;
use Backstage\Models\Content;
use Backstage\Models\Language;
use Filament\Facades\Filament;
use Filament\Tables\Actions\Action;
use Backstage\Models\ContentFieldValue;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Backstage\Jobs\Content\TranslateContent;
use Backstage\Translations\Laravel\Facades\Translator;
use Backstage\Resources\ContentResource\Pages\EditContent;
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

        $this->label(fn(): string => 'Translate');

        $this->requiresConfirmation();

        $this->modalIcon(fn(): string => 'heroicon-o-language');

        $this->modalHeading(function (array $arguments) {
            $language = Language::query()
                ->where('code', $arguments['language'])
                ->first();

            return __('Translate selected content to :language', [
                'language' => $language
                    ->native,
            ]);
        });

        $this->modalDescription(fn(): string => __('Are you sure you want to translate this content?'));

        $this->modalSubmitActionLabel(fn(): string => __('Translate'));

        $this->action(function (Collection $records, array $arguments) {
            $language = Language::query()
                ->where('code', $arguments['language'])
                ->first();

            $records->each(function (Content $record) use ($language) {
                TranslateContent::dispatch(
                    $record,
                    $language
                );
            });
        });
    }
}
