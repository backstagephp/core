<?php

namespace Backstage\Actions\Content;

use Backstage\Jobs\Content\TranslateContent;
use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Backstage\Models\Language;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Translations\Laravel\Facades\Translator;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslateContentAction extends Action
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

            return __('Translate :name (:type) to :language', [
                'name' => $this->getRecord()
                    ?->name,
                'type' => $this->getRecord()
                    ?->type
                    ?->name,
                'language' => $language
                    ->native,
            ]);
        });

        $this->modalDescription(fn(): string => __('Are you sure you want to translate this content?'));

        $this->modalSubmitActionLabel(fn(): string => __('Translate'));

        $this->action(function (Content $record, array $arguments) {
            $language = Language::query()
                ->where('code', $arguments['language'])
                ->first();

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
    }
}
