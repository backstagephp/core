<?php

namespace Backstage\Resources;

use Backstage\Media\Resources\MediaResource as Resource;
use Backstage\Translations\Laravel\Models\Language;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    public static function table(Table $table): Table
    {
        $altTagsFormSchema = self::getAltTagsFormSchema();

        return parent::table($table)
            ->headerActions([
                Action::make('upload')
                    ->modalHeading(__('Upload media'))
                    ->slideOver()
                    ->schema([
                        FileUpload::make('media')
                            ->label(__('Media'))
                            ->disk('uploadcare')
                            ->multiple(),
                    ])
                    ->action(function (array $data) {
                        // dd($data);
                        // foreach ($data['media'] as $file) {
                        //     $media = Media::create([
                        //         'url' => $media['url'],
                        //         'alt_tags' => $media['alt_tags'],
                        //     ]);
                        // }
                    }),
            ])
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
