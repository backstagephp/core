<?php

namespace Backstage\Resources;

use Backstage\Models\Content;
use Backstage\Models\MenuItem;
use Backstage\Resources\MenuItemResource\Pages;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Structure');
    }

    public static function getModelLabel(): string
    {
        return __('Menu item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Menu items');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Menu')
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        SelectTree::make('parent_ulid')
                                            ->label(__('Parent'))
                                            ->searchable()
                                            ->withCount()
                                            ->enableBranchNode()
                                            ->columnSpanFull()
                                            ->relationship(
                                                relationship: 'parent',
                                                titleAttribute: 'name',
                                                parentAttribute: 'parent_ulid',
                                                modifyQueryUsing: fn ($query) => $query->where('menu_slug', $form->getLivewire()?->getOwnerRecord()?->slug),
                                            ),

                                        SelectTree::make('content_ulid')
                                            ->label(__('Content'))
                                            ->searchable()
                                            ->withCount()
                                            ->columnSpanFull()
                                            ->enableBranchNode()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old) {
                                                $set('name', Content::find($state)->name);
                                            })
                                            ->relationship(
                                                relationship: 'content',
                                                titleAttribute: 'name',
                                                parentAttribute: 'parent_ulid',
                                            ),

                                        Toggle::make('include_children')
                                            ->label(__('Automaticly include children'))
                                            ->columnSpanFull()
                                            ->live()
                                            ->visible(fn (Get $get): bool => !empty($get('content_ulid'))),

                                        TextInput::make('name')
                                            ->requiredWithout('content_ulid')
                                            ->live(onBlur: true)
                                            ->columnSpanFull(),

                                        TextInput::make('url')
                                            ->label('URL')
                                            ->columnSpanFull()
                                            ->suffixAction(
                                                Action::make('content')
                                                    ->icon('heroicon-o-link')
                                                    ->modal()
                                                    ->modalHeading('Select Content')
                                            )
                                            ->url()
                                            ->visible(fn (Get $get): bool => empty($get('content_ulid'))),
                                    ]),
                            ]),
                        Tab::make('Advanced')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('title')
                                            ->columnSpanFull(),

                                        TextInput::make('target')
                                            ->label(__('Target'))
                                            ->columnSpanFull(),

                                        Toggle::make('active')
                                            ->label(__('Active'))
                                            ->columnSpanFull()
                                            ->default(true),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(
                        description: fn (MenuItem $record) => $record->ancestors?->implode('name', ' / ') ?? null,
                        position: 'above'
                    ),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
