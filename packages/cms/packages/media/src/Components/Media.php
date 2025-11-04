<?php

namespace Backstage\Media\Components;

use Filament\Forms\Components\FileUpload;

class Media extends FileUpload
{
    public static function make(?string $name = 'media'): static
    {
        return parent::make($name)
            ->label(__('File(s)'))
            ->disk(config('backstage.media.disk'))
            ->directory(config('backstage.media.directory'))
            ->preserveFilenames(config('backstage.media.should_preserve_filenames'))
            ->visibility(config('backstage.media.visibility'))
            ->acceptedFileTypes(config('backstage.media.accepted_file_types'))
            ->multiple()
            ->columnSpanFull();
    }
}
