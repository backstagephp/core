<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Models\Type;
use Backstage\Resources\ContentResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListContent extends ListRecords
{
    protected static string $resource = ContentResource::class;

    public ?string $type = null;

    public ?string $show = null;

    protected $queryString = ['type', 'show'];

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make(__('List'))
                    ->url(fn (): string => route('filament.backstage.resources.content.index', ['tenant' => Filament::getTenant()]))
                    ->icon('heroicon-o-bars-3')
                    ->iconPosition('before'),

                Actions\ActionGroup::make(
                    Type::orderBy('name')->get()->map(
                        fn ($type) => Actions\Action::make(__($type->name))
                            ->icon($type->icon ? 'heroicon-o-' . $type->icon : 'heroicon-o-document')
                            ->url(fn (): string => route('filament.backstage.resources.content.index', ['type' => $type->slug, 'show' => 'database', 'tenant' => Filament::getTenant()]))
                    )->toArray()
                )
                    ->label(__('Database'))
                    ->dropdownPlacement('bottom-end')
                    ->color('gray')
                    ->icon('heroicon-o-circle-stack')
                    ->iconPosition('before')
                    ->grouped(false),
            ])
                ->label($this->show == 'database' ? __('Viewed as: :type (:slug)', ['type' => __('Database'), 'slug' => $this->type]) : __('Viewed as: :type', ['type' => __('List')]))
                ->dropdownPlacement('bottom-start')
                ->color('gray')
                ->icon('heroicon-o-bars-3')
                ->iconPosition('before')
                ->button(),

            Actions\ActionGroup::make(
                Type::orderBy('name')->get()->map(
                    fn ($type) => Actions\Action::make(__($type->name))
                        ->url(fn (): string => route('filament.backstage.resources.content.create', ['type' => $type->slug, 'tenant' => Filament::getTenant()]))
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
        if ($this->type) {
            $table = ContentResource::tableDatabase($table, Type::where('slug', $this->type)->firstOrFail());
        } else {
            $table = ContentResource::table($table);
        }

        if ($this->shouldBeReorderable()) {
            return $table->reorderable('position');
        }

        return $table;
    }

    protected function handleTableFilterUpdates(): void
    {
        parent::handleTableFilterUpdates();

        $this->table = $this->table($this->makeTable());
    }

    protected function shouldBeReorderable(): bool
    {
        return isset($this->tableFilters['parent_ulid']['value']) &&
            $this->tableFilters['parent_ulid']['value'] === '0';
    }
}
