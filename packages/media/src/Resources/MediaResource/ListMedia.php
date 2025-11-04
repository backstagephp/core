<?php

namespace Backstage\Media\Resources\MediaResource;

use Backstage\Media\MediaPlugin;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    public static function getResource(): string
    {
        return MediaPlugin::get()->getResource();
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
