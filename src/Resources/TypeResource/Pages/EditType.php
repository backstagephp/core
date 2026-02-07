<?php

namespace Backstage\Resources\TypeResource\Pages;

use Backstage\Resources\TypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditType extends EditRecord
{
    protected static string $resource = TypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getRedirectUrl(): ?string
    {
        return self::getUrl([
            'record' => $this->getRecord()->getRouteKey(),
        ]);
    }
}
