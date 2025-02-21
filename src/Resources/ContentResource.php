<?php

namespace Backstage\Resources;

use Locale;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Backstage\Models\Tag;
use Backstage\Models\Type;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Backstage\Fields\Fields;
use Backstage\Models\Content;
use Backstage\Models\Language;
use Filament\Facades\Filament;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Backstage\View\Components\Filament\Badge;
use Filament\Forms\Components\DateTimePicker;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\View\Components\Filament\BadgeableColumn;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Resources\ContentResource\Pages\ListContent;
use Backstage\Resources\ContentResource\Pages\CreateContent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Backstage\Resources\ContentResource\Pages\ListContentMetaTags;
use Backstage\Resources\ContentResource\Pages\ManageChildrenContent;

class ContentResource extends Resource
{
    use CanMapDynamicFields {
        resolveFormFields as private traitResolveFormFields;
        resolveFieldInput as private traitResolveFieldInput;
    }

    protected static ?string $model = Content::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?Type $type = null;

    public static function getModelLabel(): string
    {
        return __('Content');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Content');
    }

    public static function getSlug(): string
    {
        return 'content';
    }

    public static function getNavigationItems(): array
    {

        $contentTypes = Type::orderBy('name')->get()->map(function (Type $type) {
            return NavigationItem::make($type->slug)
                ->label($type->name_plural)
                ->parentItem('Content')
                ->isActiveWhen(fn (NavigationItem $item) => request()->input('tableFilters.type_slug.values.0') === $type->slug)
                ->url(route('filament.backstage.resources.content.index', [
                    'tenant' => Filament::getTenant(),
                    'tableFilters[type_slug][values]' => [$type->slug],
                ]));
        })->toArray();

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn () => request()->routeIs(static::getRouteBaseName() . '.*') && ! request()->input('tableFilters.type_slug.values.0') && ! request()->is('*/meta-tags'))
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
            ...$contentTypes,
            NavigationItem::make('meta_tags')
                ->label('Meta Tags')
                ->icon('heroicon-o-code-bracket-square')
                ->group('SEO')
                ->isActiveWhen(fn (NavigationItem $item) => request()->routeIs('filament.backstage.resources.content.meta_tags'))
                ->url(route('filament.backstage.resources.content.meta_tags', ['tenant' => Filament::getTenant()])),
        ];
    }

    public static function form(Form $form): Form
    {
        self::$type = Type::firstWhere('slug', ($form->getLivewire()->data['type_slug'] ?? $form->getRecord()->type_slug));

        return $form
            ->schema([
                TextInput::make('name')
                    ->hiddenLabel()
                    ->placeholder(__('Name'))
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'font-size: 30px'])
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Content $record) {
                        $set('meta_tags.title', $state);
                        
                        if (!$record || blank($get('path'))) {
                            $set('path', Str::slug($state));
                        }

                        $currentSlug = $get('slug');

                        if (! $record?->slug && (! $currentSlug || $currentSlug === Str::slug($old))) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                Grid::make(12)
                    ->schema([
                        Tabs::make('Tabs')
                            ->columnSpan(8)
                            ->tabs([
                                Tab::make(self::$type->slug)
                                    ->label(__(self::$type->name))
                                    ->schema([
                                        Hidden::make('type_slug')
                                            ->default(self::$type->slug),
                                        Grid::make()
                                            ->columns(1)
                                            ->schema(self::getTypeInputs()),
                                    ]),
                                Tab::make('seo')
                                    ->label('SEO')
                                    ->schema([
                                        TextInput::make('meta_tags.title')
                                            ->label('Page Title')
                                            ->columnSpanFull(),
                                        TextInput::make('meta_tags.description')
                                            ->label('Description')
                                            ->helperText('Meta description for search engines.')
                                            ->columnSpanFull(),
                                        TagsInput::make('meta_tags.keywords')
                                            ->label('Keywords')
                                            ->helperText('Meta keywords are not used by search engines anymore, but use it to define focus keywords.')
                                            ->color('gray')
                                            ->columnSpanFull()
                                            ->reorderable()
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->suggestions(Content::whereJsonLength('meta_tags->keywords', '>', 0)->orderBy('edited_at')->take(25)->get()->map(fn ($content) => $content->meta_tags['keywords'])->flatten()->filter()),
                                    ]),
                            ]),
                            
                        Hidden::make('language_code')
                            ->default(Language::where('active', 1)->count() === 1 ? Language::where('active', 1)->first()->code : Language::where('active', 1)->where('default', true)->first()?->code),
                            
                        Tabs::make()
                            ->columnSpan(4)
                            ->tabs([
                                Tab::make('content')
                                    ->label('Content')
                                    ->schema([
                                        SelectTree::make('parent_ulid')
                                            ->label(__('Parent'))
                                            ->placeholder(__('Select parent content'))
                                            ->searchable()
                                            ->withCount()
                                            ->rules([
                                                Rule::exists('content', 'ulid')
                                                    ->where('language_code', $form->getLivewire()->data['language_code'] ?? null),
                                            ])
                                            ->relationship(
                                                relationship: 'parent',
                                                titleAttribute: 'name',
                                                parentAttribute: 'parent_ulid',
                                                modifyQueryUsing: function (EloquentBuilder $query, $record) use ($form) {
                                                    return $query->when($form->getLivewire()->data['language_code'] ?? null, function ($query, $languageCode) {
                                                        $query->where('language_code', $languageCode);
                                                    });
                                                },
                                            )
                                            ->disabledOptions(fn ($record) => [$record?->getKey()]),
                                            
                                        TextInput::make('path')
                                            ->columnSpanFull()
                                            ->rules(function (Get $get, $record) {
                                                if ($get('public') === false && $record) {
                                                    return [];
                                                }
                        
                                                return Rule::unique('content', 'path')->ignore($record?->getKey(), $record?->getKeyName());
                                            })
                                            ->prefix($form->getRecord()?->path_prefix ? $form->getRecord()->path_prefix : '/')
                                            ->formatStateUsing(fn (?Content $record) => ltrim($record->path ?? '', '/')),

                                        Select::make('language_code')
                                            ->label(__('Language'))
                                            ->columnSpanFull()
                                            ->placeholder(__('Select Language'))
                                            ->options(
                                                Language::where('active', 1)
                                                    ->get()
                                                    ->sort()
                                                    ->groupBy(function ($language) {
                                                        return Str::contains($language->code, '-') ? Locale::getDisplayRegion('-' . strtolower(explode('-', $language->code)[1]), app()->getLocale()) : 'Worldwide';
                                                    })
                                                    ->mapWithKeys(fn ($languages, $countryName) => [
                                                        $countryName => $languages->mapWithKeys(fn ($language) => [
                                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' . $countryName . ')',
                                                        ])->toArray(),
                                                    ])
                                            )
                                            ->allowHtml()
                                            ->visible(fn () => Language::where('active', 1)->count() > 1),

                                        TextInput::make('slug')
                                            ->columnSpanFull()
                                            ->helperText('Unique string identifier for this content.')
                                            ->required(),
                                            
                                        Toggle::make('pin')
                                            ->label('Pin')
                                            ->inline(false)
                                            ->onIcon('heroicon-s-check')
                                            ->offIcon('heroicon-s-x-mark')
                                            ->helperText('Pin content to the top of lists.')
                                            ->columnSpanFull(),
                                            
                                        TagsInput::make('tags')
                                            ->color('gray')
                                            ->columnSpanFull()
                                            ->helperText('Add tags to group content.')
                                            ->tagPrefix('#')
                                            ->reorderable()
                                            ->formatStateUsing(fn ($state, ?Content $record) => $state ?: $record?->tags->pluck('name')->toArray() ?: [])
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->suggestions(Tag::orderBy('updated_at', 'desc')->take(25)->pluck('name')),
                                    ]),
                                Tab::make('publication')
                                    ->label('Publication')
                                    ->schema([
                                        DateTimePicker::make('published_at')
                                            ->columnSpanFull()
                                            ->date()
                                            ->default(now()->format('dd/mm/YYYY'))
                                            ->displayFormat('M j, Y - H:i')
                                            ->formatStateUsing(fn (?Content $record) => $record ? $record->published_at : now())
                                            ->label('Publication date')
                                            ->helperText('Set a date in past or future to schedule publication.')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar-days')
                                            ->seconds(false),
                                            
                                        DateTimePicker::make('expired_at')
                                            ->label('Expiration date')
                                            ->date()
                                            ->prefixIcon('heroicon-o-calendar')
                                            ->native(false)
                                            ->columnSpanFull()
                                            ->helperText('Set date in future to auto-expire publication.'),
                                    ]),
                                Tab::make('advanced')
                                    ->label('Advanced')
                                    ->schema([
                                        Toggle::make('public')
                                            ->label('Public')
                                            ->default(fn () => self::$type->public ?? true)
                                            ->onIcon('heroicon-s-check')
                                            ->offIcon('heroicon-s-x-mark')
                                            ->inline(false)
                                            ->helperText(__('Make content publicly accessible on path.'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private static function resolveFormFields(mixed $record = null): array
    {
        $instance = new self;

        return $instance->traitResolveFormFields($record);
    }

    private static function resolveFieldInput(mixed $field, Collection $customFields, mixed $record = null, bool $isNested = false): ?object
    {
        $instance = new self;

        return $instance->traitResolveFieldInput($field, $customFields, $record, $isNested);
    }

    public static function getTypeInputs()
    {
        return collect(self::$type->fields)
            ->filter(fn ($field) => self::$type->name_field !== $field->slug)
            ->map(function ($field) {
                return self::resolveFieldInput($field, collect(Fields::getFields()), self::$type);
            })
            ->filter()
            ->toArray();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->separator('')
                    ->description(
                        description: fn (Content $record) => $record->ancestors?->implode('name', ' / ') ?? null,
                        position: 'above'
                    )
                    ->suffixBadges([
                        Badge::make('type')
                            ->label(fn (Content $record) => $record->type->name)
                            ->color('gray'),
                    ]),
                ImageColumn::make('authors')
                    ->circular()
                    ->stacked()
                    ->ring(2)
                    ->getStateUsing(fn (Content $record) => collect($record->authors)->pluck('avatar_url')->toArray())
                    ->limit(3),
                ImageColumn::make('language_code')
                    ->label('Language')
                    ->getStateUsing(fn (Content $record) => explode('-', $record->language_code)[0])
                    ->view('backstage::filament.tables.columns.language-flag-column')
                    ->visible(fn () => Language::where('active', 1)->count() > 1),
                ViewColumn::make('country_code')
                    ->label('Country')
                    ->getStateUsing(fn (Content $record) => strtolower(explode('-', $record->language_code)[1] ?? 'Worldwide'))
                    ->view('backstage::filament.tables.columns.country-flag-column')
                    ->visible(fn () => Language::where('active', 1)->distinct(DB::raw('SUBSTRING_INDEX(code, "-", -1)'))->count() > 1),
                TextColumn::make('edited_at')
                    ->since()
                    ->alignEnd()
                    ->sortable(),
            ])
            ->modifyQueryUsing(
                fn (EloquentBuilder $query) => $query->with('ancestors', 'authors', 'type')
            )
            ->defaultSort('edited_at', 'desc')
            ->filters([
                Filter::make('locale')
                    ->form([
                        Select::make('language_code')
                            ->label(__('Language'))
                            ->columnSpanFull()
                            ->placeholder(__('Select Language'))
                            ->options(
                                Language::where('active', 1)
                                    ->get()
                                    ->sort()
                                    ->groupBy(function ($language) {
                                        return Str::contains($language->code, '-') ? Locale::getDisplayRegion('-' . strtolower(explode('-', $language->code)[1]), app()->getLocale()) : 'Worldwide';
                                    })
                                    ->mapWithKeys(fn ($languages, $countryName) => [
                                        $countryName => $languages->mapWithKeys(fn ($language) => [
                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' .$countryName. ')',
                                        ])->toArray(),
                                    ])
                            )
                            ->allowHtml(),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query->when($data['language_code'] ?? null, function ($query, $languageCode) {
                            return $query->where('language_code', $languageCode);
                        });
                    })
                    ->visible(fn () => Language::where('active', 1)->count() > 1),
                SelectFilter::make('type_slug')
                    ->label('Type')
                    ->native(false)
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('type', 'name'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->native(false)
                    ->options([])
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('public')
                    ->label('Public')
                    ->native(false)
                    ->options([
                        'true' => 'Yes',
                        'false' => 'No',
                    ]),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->label('Tags')
                    ->native(false)
                    ->preload()
                    ->multiple(),
                Filter::make('date')
                    ->form([
                        Fieldset::make('Date')
                            ->schema([
                                Select::make('date_column')
                                    ->columnSpanFull()
                                    ->options([
                                        'created_at' => 'Created',
                                        'edited_at' => 'Edited',
                                        'expired_at' => 'Expired',
                                        'published_at' => 'Published',
                                    ])
                                    ->default('created_at')
                                    ->native(false),
                                DatePicker::make('date_from')
                                    ->native(false),
                                DatePicker::make('date_until')
                                    ->native(false),
                            ]),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn (EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate($data['date_column'], '>=', $date),
                            )
                            ->when(
                                $data['date_until'] ?? null,
                                fn (EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate($data['date_column'], '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersFormWidth('md')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter')
                    ->slideOver(),
            )
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContent::route('/'),
            'create' => CreateContent::route('/create/{type}'),
            'edit' => EditContent::route('/{record}/edit'),
            'meta_tags' => ListContentMetaTags::route('/meta-tags'),
            'children' => ManageChildrenContent::route('/{record}/children'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditContent::class,
            ManageChildrenContent::class,
        ]);
    }
}
