<?php

namespace Backstage\Resources\MenuResource\RelationManagers;

use Backstage\Resources\MenuItemResource;
use Backstage\View\Components\Filament\Badge;
use Backstage\View\Components\Filament\BadgeableColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public $parentFilter = null;

    public function form(Schema $schema): Schema
    {
        return MenuItemResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(function (Builder $query) {
                // If parentFilter is set, show children of that parent
                if ($this->parentFilter) {
                    $query->where('parent_ulid', $this->parentFilter);
                } else {
                    // Otherwise show only parents (top-level items)
                    $query->whereNull('parent_ulid');
                }

                $query->withCount('children');
            })
            ->description(function () {
                if ($this->parentFilter) {
                    $parent = \Backstage\Models\MenuItem::find($this->parentFilter);
                    if ($parent) {
                        return 'Showing children of: ' . $parent->name;
                    }
                }

                return null;
            })
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                BadgeableColumn::make('name')
                    ->grow(false)
                    ->separator('')
                    ->suffixBadges(function ($record) {
                        if ($record && $record->children_count > 0) {
                            return [
                                Badge::make('children')
                                    ->label('Contains underlying menu items')
                                    ->color('primary'),
                            ];
                        }

                        return [];
                    })
                    ->action(function ($record) {
                        if ($record && $record->children_count > 0) {
                            $this->parentFilter = $record->ulid;
                        }
                    }),
            ])
            ->filters([
                //
            ])
            ->recordAction(null)
            ->headerActions([
                CreateAction::make(),
                Action::make('back_to_parents')
                    ->label('← Back to parents')
                    ->color('gray')
                    ->visible(fn () => $this->parentFilter !== null)
                    ->action(function () {
                        $this->parentFilter = null;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
