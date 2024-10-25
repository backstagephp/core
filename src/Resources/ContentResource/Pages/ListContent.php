<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Vormkracht10\Backstage\Models\Type;
use Filament\Resources\Pages\ListRecords;
use Vormkracht10\Backstage\Resources\ContentResource;

class ListContent extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make(
                Type::orderBy('name')->get()->map(
                    fn($type) => Actions\Action::make(__($type->name))
                        ->icon($type->icon ?? 'heroicon-o-star')
                        ->url('')
                )->toArray()
            )
                ->label(__('New content'))
                ->dropdownPlacement('bottom-end')
                ->icon('heroicon-o-chevron-down')
                ->iconPosition('after')
                ->button(),
        ];
    }
}
