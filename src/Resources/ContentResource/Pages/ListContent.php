<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Resources\ContentResource;

class ListContent extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make(
                Type::orderBy('name')->get()->map(
                    fn ($type) => CreateAction::make()
                        ->label(__($type->name))
                        ->url(fn () : string => route('filament.backstage.resources.content.create', ['type' => $type]))
                        ->slideOver()
                        ->modalWidth('6xl')
                )->toArray()
            )
                ->label(__('New Content'))
                ->dropdownPlacement('bottom-end')
                ->icon('heroicon-o-chevron-down')
                ->iconPosition('after')
                ->button(),
        ];
    }
}
