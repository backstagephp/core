<?php

namespace Backstage\Resources;

use Backstage\Models\Content;
use Backstage\Models\MenuItem;
use Backstage\Resources\MenuItemResource\Pages\CreateMenuItem;
use Backstage\Resources\MenuItemResource\Pages\EditMenuItem;
use Backstage\Resources\MenuItemResource\Pages\ListMenuItems;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Menu')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('parent_ulid')
                                            ->label('Parent')
                                            ->searchable()
                                            ->relationship(
                                                name: 'parent',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: function ($query, $livewire) use ($schema) {
                                                    // Try to get menu_slug from the current record
                                                    $menuSlug = $schema->getRecord()?->menu_slug;

                                                    // If not available (create mode in RelationManager), get from owner
                                                    if (!$menuSlug && method_exists($livewire, 'getOwnerRecord')) {
                                                        $owner = $livewire->getOwnerRecord();
                                                        if ($owner) {
                                                            $menuSlug = $owner->slug;
                                                        }
                                                    }

                                                    if ($menuSlug) {
                                                        $query->where('menu_slug', $menuSlug);
                                                    }

                                                    if ($schema->getRecord()?->ulid) {
                                                        $query->where('ulid', '!=', $schema->getRecord()->ulid);
                                                    }

                                                    return $query->orderBy('name');
                                                }
                                            )
                                            ->preload()
                                            ->columnSpan(2),

                                        TextInput::make('name')
                                            ->columnSpan(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                                $set('slug', Str::slug($state));
                                            }),

                                        TextInput::make('slug')
                                            ->columnSpan(1)
                                            ->required()
                                            ->unique(ignoreRecord: true),

                                        Hidden::make('content_ulid'),

                                        TextInput::make('url')
                                            ->label('URL')
                                            ->columnSpan(2)
                                            ->suffixActions([
                                                Action::make('link_content')
                                                    ->icon('heroicon-o-link')
                                                    ->modal()
                                                    ->modalHeading('Select Content')
                                                    ->modalWidth('2xl')
                                                    ->schema(
                                                        fn (Schema $schema) => $schema
                                                            ->components([
                                                                Select::make('content_ulid')
                                                                    ->label('Content')
                                                                    ->searchable()
                                                                    ->preload()
                                                                    ->searchingMessage([__('Searching in the script...'), __('Looking behind the curtain...'), __('Searching the archives...'), __('Searching the library...'), __('Searching the vault...'), __('Searching behind the scenes...')][rand(0, 5)])
                                                                    ->options(function (?string $state) {
                                                                        $contentQuery = Content::query();

                                                                        return $contentQuery->pluck('name', 'ulid')->toArray();
                                                                    })
                                                                    ->getSearchResultsUsing(fn (string $search): array => Content::where('name', 'like', "%{$search}%")->limit(10)->pluck('name', 'ulid')->toArray())
                                                                    ->required(),
                                                            ])
                                                    )
                                                    ->action(function (array $data, Set $set) {
                                                        $content = Content::where('ulid', $data['content_ulid'])->first();
                                                        if ($content) {
                                                            $set('url', $content->url);
                                                            $set('content_ulid', $content->ulid);
                                                        }
                                                    }),
                                            ])
                                            ->required(),

                                        Checkbox::make('target')
                                            ->label('Open in new tab')
                                            ->default(false)
                                            ->afterStateHydrated(fn (Checkbox $component, ?MenuItem $record) => $component->state($record?->target === '_blank'))
                                            ->dehydrateStateUsing(fn (bool $state): string => $state ? '_blank' : '_self')
                                            ->columnSpan(1),
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
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
