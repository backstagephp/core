<?php

namespace Backstage\Resources\LanguageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Backstage\Resources\LanguageResource;

class EditLanguage extends EditRecord
{
    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
