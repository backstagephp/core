<?php

namespace Vormkracht10\Backstage\Resources\BlockResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Resources\BlockResource;

class ListBlocks extends ListRecords
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make(
                Type::orderBy('name')->get()->map(
                    fn ($type) => Actions\Action::make(__($type->name))
                        ->form(fn (Form $form) => BlockResource::form($form)->getComponents())
                        ->slideOver()
                        ->modalWidth('6xl')
                        ->icon($type->icon ? 'heroicon-o-' . $type->icon : 'heroicon-o-document')
                )->toArray()
            )
                ->label(__('New Block'))
                ->dropdownPlacement('bottom-end')
                ->icon('heroicon-o-chevron-down')
                ->iconPosition('after')
                ->button(),
        ];
    }
}
