<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Vormkracht10\Backstage\Models\Type;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextInputColumn;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Resources\ContentResource;

class ListContentMetaTags extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make(
                Type::orderBy('name')->get()->map(
                    fn($type) => Actions\Action::make(__($type->name))
                        ->url(fn(): string => route('filament.backstage.resources.content.create', ['type' => $type->slug, 'tenant' => Filament::getTenant()]))
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

    public function table(Table $table): Table
    {
        return static::getResource()::table($table)
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->state(function (Content $record): string {
                        return $record->name;
                    })
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('meta_tags.title')
                    ->label('Page title')
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('meta_tags.description')
                    ->label('Meta description')
                    ->searchable()
                    ->sortable(),
                TextInputColumn::make('meta_tags.keywords')
                    ->label('Meta keywords')
                    ->searchable()
                    ->sortable(),
            ]);
    }
}
