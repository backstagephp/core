<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Models\Type;
use Backstage\Resources\ContentResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListContent extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make(
                Type::orderBy('name')->get()->map(
                    fn ($type) => Actions\Action::make(__($type->name))
                        ->url(fn (): string => route('filament.backstage.resources.content.create', ['type' => $type->slug, 'tenant' => Filament::getTenant()]))
                        ->slideOver()
                        ->modalWidth('6xl')
                        ->icon($type->icon ? 'heroicon-o-' . $type->icon : 'heroicon-o-document')
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
