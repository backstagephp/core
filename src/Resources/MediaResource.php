<?php

namespace Backstage\Resources;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Backstage\Translations\Laravel\Models\Language;
use Backstage\Media\Resources\MediaResource as Resource;

class MediaResource extends Resource
{
    public static function table(Table $table): Table
    {
        $altTagsFormSchema = self::getAltTagsFormSchema();

        return parent::table($table)
            ->recordActions([
                ...parent::table($table)->getRecordActions(),
                Action::make('alt-tags')
                    ->modalHeading(__('Set alt tags for this media'))
                    ->hiddenLabel()
                    ->icon('heroicon-o-tag')
                    ->tooltip(__('Set alt Tags'))
                    ->slideOver()
                    ->schema([
                        ImageEntry::make('url')
                            ->label(__('Media'))
                            ->formatStateUsing(fn ($state) => $state ? url($state) : null)
                            ->height(200),
                        Grid::make(2)
                            ->schema([
                                ...$altTagsFormSchema,
                            ]),
                    ]),
            ]);
    }

    private static function getAltTagsFormSchema(): array
    {
        $schema = [];

        foreach (Language::all() as $language) {
            $schema[] = TextInput::make('alt_tags_' . $language->code)
                ->label(__('Alt Tag') . ' (' . $language->name . ')')
                ->prefixIcon(country_flag($language->code));
        }

        return $schema;
    }
}
