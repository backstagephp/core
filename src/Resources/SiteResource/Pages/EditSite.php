<?php

namespace Backstage\Resources\SiteResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Backstage\Resources\SiteResource;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
