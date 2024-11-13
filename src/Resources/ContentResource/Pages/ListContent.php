<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
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
                    fn ($type) => Actions\Action::make(__($type->name))
                        ->form(fn (Form $form) => ContentResource::form($form)->getComponents())
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
