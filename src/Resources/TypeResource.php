<?php

namespace Backstage\Resources;

use Backstage\Fields\Filament\RelationManagers\FieldsRelationManager;
use Backstage\Models\Content;
use Backstage\Models\Type;
use Backstage\Resources\TypeResource\Pages;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TypeResource extends Resource
{
    protected static ?string $model = Type::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'sites';

    public static function getNavigationGroup(): ?string
    {
        return __('Structure');
    }

    public static function getModelLabel(): string
    {
        return __('Type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Types');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make(__('Type'))
                            ->schema([

                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Type $record) {
                                        $set('name_plural', Str::plural($state));

                                        $currentSlug = $get('slug');

                                        if (! $record?->slug && (! $currentSlug || $currentSlug === Str::slug($old))) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                TextInput::make('name_plural')
                                    ->required(),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->rules([
                                        fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                            if (in_array(strtolower($value), ['content', 'advanced', 'default'])) {
                                                $fail(__('This :attribute cannot be used.', ['attribute' => 'slug']));
                                            }
                                        },
                                    ]),
                                ToggleButtons::make('icon')
                                    ->default('circle-stack')
                                    ->options([
                                        'circle-stack' => '',
                                        'light-bulb' => '',
                                    ])
                                    ->icons([
                                        'circle-stack' => 'heroicon-o-circle-stack',
                                        'light-bulb' => 'heroicon-o-light-bulb',
                                    ])
                                    ->inline()
                                    ->grouped()
                                    ->required(),
                                Toggle::make('public')
                                    ->label(__('Public'))
                                    ->inline(false),
                            ])->columns(3),
                        Tabs\Tab::make(__('Settings'))
                            ->schema([
                                Fieldset::make('Sorting')
                                    ->schema([
                                        Select::make('sort_column')
                                            ->label(__('Column'))
                                            ->searchable()
                                            ->preload()
                                            ->options([
                                                'approved_at' => 'Approved At',
                                                'created_at' => 'Created At',
                                                'edited_at' => 'Edited At',
                                                'name' => 'Name',
                                                'pinned_at' => 'Pinned At',
                                                'position' => 'Position',
                                                'published_at' => 'Published At',
                                                'refreshed_at' => 'Refreshed At',
                                                'updated_at' => 'Updated At',
                                            ]),
                                        Select::make('sort_direction')
                                            ->label(__('Direction'))
                                            ->options([
                                                'asc' => 'Ascending',
                                                'desc' => 'Descending',
                                            ]),
                                    ])->columns(2),
                                Fieldset::make(__('Parent selection'))
                                    ->schema([
                                        Toggle::make('parent_required')
                                            ->label(__('Parent Required'))
                                            ->helperText(__('If enabled, all content of this type must have a parent.'))
                                            ->live()
                                            ->inline(false),
                                        Repeater::make('parent_filters')
                                            ->label(__('Filters'))
                                            ->live()
                                            ->visible(fn (Get $get): bool => $get('parent_required'))
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('column')
                                                            ->options(function (Get $get) {
                                                                $columns = Schema::getColumnListing((new Content)->getTable());

                                                                // Create options array with column names
                                                                $columnOptions = collect($columns)->mapWithKeys(function ($column) {
                                                                    return [$column => Str::title($column)];
                                                                })->toArray();

                                                                return $columnOptions;
                                                            })
                                                            ->live()
                                                            ->label(__('Column')),
                                                        Select::make('operator')
                                                            ->options([
                                                                '=' => __('Equal'),
                                                                '!=' => __('Not equal'),
                                                                '>' => __('Greater than'),
                                                                '<' => __('Less than'),
                                                                '>=' => __('Greater than or equal to'),
                                                                '<=' => __('Less than or equal to'),
                                                                'LIKE' => __('Like'),
                                                                'NOT LIKE' => __('Not like'),
                                                            ])
                                                            ->label(__('Operator')),
                                                        TextInput::make('value')
                                                            ->datalist(function (Get $get) {
                                                                $column = $get('column');

                                                                if (! $column) {
                                                                    return [];
                                                                }

                                                                return Content::query()
                                                                    ->select($column)
                                                                    ->distinct()
                                                                    ->pluck($column)
                                                                    ->toArray();
                                                            })
                                                            ->label(__('Value')),
                                                    ]),
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('icon')
                    ->label('')
                    ->width(1)
                    ->icon(fn (string $state): string => 'heroicon-o-' . $state),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
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
            FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTypes::route('/'),
            'create' => Pages\CreateType::route('/create'),
            'edit' => Pages\EditType::route('/{record}/edit'),
        ];
    }
}
