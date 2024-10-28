<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
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
                    fn ($type) => Actions\Action::make(__($type->name))
                        ->slideOver()
                        ->icon($type->icon ? 'heroicon-o-' . $type->icon : 'heroicon-o-document')
                        ->url(route('filament.backstage.resources.content.create', ['type' => $type->slug]))
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
