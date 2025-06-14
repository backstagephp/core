<?php

namespace Backstage\Resources;

use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Fields\Fields;
use Backstage\Fields\Fields\RichEditor;
use Backstage\Models\Content;
use Backstage\Models\Language;
use Backstage\Models\Tag;
use Backstage\Models\Type;
use Backstage\Models\User;
use Backstage\Resources\ContentResource\Pages\CreateContent;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Resources\ContentResource\Pages\ListContent;
use Backstage\Resources\ContentResource\Pages\ListContentMetaTags;
use Backstage\Resources\ContentResource\Pages\ManageChildrenContent;
use Backstage\View\Components\Filament\Badge;
use Backstage\View\Components\Filament\BadgeableColumn;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
                ->parentItem(__('Content'))
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
                ->label(__('Meta Tags'))
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
                    ->placeholder(__('Name'))
                    ->columnSpanFull()
                    // ->withAI(hint: true)
                    ->canTranslate(hint: true)
                    ->extraInputAttributes(['style' => 'font-size: 30px'])
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Content $record) {
                        $set('meta_tags.title', $state);

                        $path = ($get('parent_ulid') ? Content::find($get('parent_ulid'))->path . '/' : '') . Str::slug($state);
                        $set('path', $path);

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
                                    ->icon('heroicon-o-' . self::$type->icon)
                                    ->label(__(self::$type->name))
                                    ->schema([
                                        Hidden::make('type_slug')
                                            ->default(self::$type->slug),
                                        Grid::make()
                                            ->columns(1)
                                            ->schema(function () {
                                                return self::getTypeInputs();
                                            }),
                                    ]),
                                Tab::make('meta')
                                    ->label(__('Meta'))
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->schema([
                                        TextInput::make('path')
                                            ->columnSpanFull()
                                            ->rules(function (Get $get, $record) {
                                                if ($get('public') === false && $record) {
                                                    return [];
                                                }

                                                return Rule::unique('content', 'path')
                                                    ->where('language_code', $get('language_code'))
                                                    ->ignore($record?->getKey(), $record?->getKeyName());
                                            })
                                            ->prefix($form->getRecord()?->path_prefix ? $form->getRecord()->path_prefix : '/')
                                            ->formatStateUsing(fn (?Content $record) => ltrim($record->path ?? '', '/')),

                                        TextInput::make('meta_tags.title')
                                            ->label(__('Page Title'))
                                            ->columnSpanFull(),

                                        TextInput::make('meta_tags.description')
                                            ->label(__('Description'))
                                            ->helperText('Meta description for search engines.')
                                            ->columnSpanFull(),

                                        Select::make('meta_tags.robots')
                                            ->label(__('Robots'))
                                            ->options(['noindex' => __('Do not index this content (noindex)'), 'nofollow' => __('Do not follow links (nofollow)'), 'noarchive' => __('Do not archive this content (noarchive)'), 'nosnippet' => __('No description in search results (nosnippet)'), 'noodp' => __('Do not index this in Open Directory Project (noodp)')])
                                            ->multiple()
                                            ->columnSpanFull(),

                                        TagsInput::make('meta_tags.keywords')
                                            ->label(__('Keywords'))
                                            ->helperText('Meta keywords are not used by search engines anymore, but use it to define focus keywords.')
                                            ->color('gray')
                                            ->columnSpanFull()
                                            ->reorderable()
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->suggestions(Content::whereJsonLength('meta_tags->keywords', '>', 0)->orderBy('edited_at')->take(25)->get()->map(fn ($content) => $content->meta_tags['keywords'])->flatten()->filter()),
                                    ]),
                                Tab::make('open-graph')
                                    ->label(__('Open Graph'))
                                    ->icon('heroicon-o-photo')
                                    ->schema([]),
                                Tab::make('microdata')
                                    ->label(__('Microdata'))
                                    ->icon('heroicon-o-code-bracket-square')
                                    ->schema([]),
                                Tab::make('template')
                                    ->label(__('Template'))
                                    ->icon('heroicon-o-clipboard')
                                    ->schema([]),
                            ])
                            ->id('content')
                            ->persistTabInQueryString(),

                        Hidden::make('language_code')
                            ->default(Language::active()->count() === 1 ? Language::active()->first()->code : Language::active()->where('default', true)->first()?->code),

                        Tabs::make()
                            ->columnSpan(4)
                            ->tabs([
                                Tab::make('details')
                                    ->label(__('Details'))
                                    ->schema([
                                        SelectTree::make('parent_ulid')
                                            ->label(__('Parent'))
                                            ->placeholder(__('Select parent content'))
                                            ->searchable()
                                            ->withCount()
                                            ->required(fn () => self::$type->parent_required)
                                            ->key(fn (Get $get) => 'parent_ulid_' . ($get('language_code') ?? ''))
                                            ->rules([
                                                Rule::exists('content', 'ulid')
                                                    ->where('language_code', $form->getLivewire()->data['language_code'] ?? null),
                                            ])
                                            ->enableBranchNode()
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
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?Content $record) {
                                                if ($state) {
                                                    $parent = Content::find($state);

                                                    if ($parent->path && $get('path')) {
                                                        $set('path', $parent->path . '/' . $get('path'));
                                                    }
                                                }
                                            })
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
                                                Language::active()
                                                    ->get()
                                                    ->sort()
                                                    ->groupBy(function ($language) {
                                                        return Str::contains($language->code, '-') ? localized_country_name($language->code) : __('Worldwide');
                                                    })
                                                    ->mapWithKeys(fn ($languages, $countryName) => [
                                                        $countryName => $languages->mapWithKeys(fn ($language) => [
                                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ' . localized_language_name($language->code) . ' (' . $countryName . ')',
                                                        ])->toArray(),
                                                    ])
                                            )
                                            ->allowHtml()
                                            ->visible(fn () => Language::active()->count() > 1)
                                            ->live(),

                                        TextInput::make('slug')
                                            ->columnSpanFull()
                                            ->helperText('Unique string identifier for this content.')
                                            ->required(),

                                        Toggle::make('pin')
                                            ->label(__('Pin'))
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
                                    ->label(__('Publication'))
                                    ->schema([
                                        DateTimePicker::make('published_at')
                                            ->columnSpanFull()
                                            ->date()
                                            ->default(now()->format('dd/mm/YYYY'))
                                            ->displayFormat('M j, Y - H:i')
                                            ->formatStateUsing(fn (?Content $record) => $record ? $record->published_at : now())
                                            ->label(__('Publication date'))
                                            ->helperText('Set a date in past or future to schedule publication.')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar-days')
                                            ->seconds(false),

                                        DateTimePicker::make('expired_at')
                                            ->label(__('Expiration date'))
                                            ->date()
                                            ->prefixIcon('heroicon-o-calendar')
                                            ->native(false)
                                            ->columnSpanFull()
                                            ->helperText('Set date in future to auto-expire publication.'),
                                    ]),

                                Tab::make('advanced')
                                    ->label(__('Options'))
                                    ->schema([
                                        Toggle::make('public')
                                            ->label(__('Public'))
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
        $groups = [];
        collect(self::$type->fields)
            ->filter(fn ($field) => self::$type->name_field !== $field->slug)
            ->each(function ($field) use (&$groups) {
                $resolvedField = self::resolveFieldInput($field, collect(Fields::getFields()), self::$type);
                if ($resolvedField) {
                    $groups[$field->group ?? null][] = $resolvedField;
                }
            });

        return collect($groups)->map(function ($fields, $group) {
            if (empty($group)) {
                return Grid::make(1)->schema($fields);
            }

            return Section::make($group)
                ->collapsible()
                ->collapsed()
                ->compact()
                ->label(__($group))
                ->schema([
                    Grid::make(1)->schema($fields),
                ]);
        })->values()->toArray();
    }

    public static function tableDatabase(Table $table, Type $type): Table
    {
        return $table
            ->columns([
                IconColumn::make('published')
                    ->label(__(''))
                    ->width(1)
                    ->icon(fn (string $state): string => match ($state) {
                        'draft' => 'heroicon-o-pencil-square',
                        'expired' => 'heroicon-o-x-mark-circle',
                        'published' => 'heroicon-o-check-circle',
                        'scheduled' => 'heroicon-o-calendar-days',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'expired' => 'danger',
                        'published' => 'success',
                        'scheduled' => 'info',
                        default => 'gray',
                    })
                    ->size(IconColumn\IconColumnSize::Medium)
                    ->getStateUsing(fn (Content $record) => $record->published_at ? 'published' : 'draft'),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('language_code')
                    ->label('')
                    ->width(50)
                    ->getStateUsing(fn (Content $record) => explode('-', $record->language_code)[0])
                    ->view('backstage::filament.tables.columns.language-flag-column')
                    ->visible(fn () => Language::active()->count() > 1),

                ViewColumn::make('country_code')
                    ->label('')
                    ->width(50)
                    ->getStateUsing(fn (Content $record) => strtolower(explode('-', $record->language_code)[1]) ?? __('Worldwide'))
                    ->view('backstage::filament.tables.columns.country-flag-column')
                    ->visible(fn () => Language::active()->where('code', 'LIKE', '%-%')->distinct(DB::raw('SUBSTRING_INDEX(code, "-", -1)'))->count() > 1),

                ...$type
                    ->fields
                    ->whereIn('field_type', ['text'])
                    ->map(function ($field) {
                        if ($field->field_type === 'text') {
                            return TextInputColumn::make($field->slug)
                                ->getStateUsing(fn ($record) => $record->values->where('field_ulid', $field->ulid)->first()?->value)
                                ->updateStateUsing(function (Set $set, Get $get, ?string $state, ?Content $record) use ($field) {
                                    if ($state === null) {
                                        return;
                                    }

                                    $record->values->where('field_ulid', $field->ulid)->first()->update([
                                        'value' => $state,
                                    ]);
                                });
                        }
                    })
                    ->toArray(),
            ])
            ->modifyQueryUsing(
                fn (EloquentBuilder $query) => $query->with('ancestors', 'authors', 'type', 'values')->where('type_slug', $type->slug)
            )
            ->defaultSort($type->sort_column ?? 'position', $type->sort_direction ?? 'desc')
            ->actions([
                ...$type
                    ->fields
                    ->whereIn('field_type', ['rich-editor'])
                    ->map(
                        fn ($field) => Action::make($field->slug)
                            ->label(__('Edit :name', ['name' => $field->name]))
                            ->modal()
                            ->mountUsing(function (Form $form, Content $record) use ($field) {
                                $value = $record->values->where('field_ulid', $field->ulid)->first();

                                $form->fill([
                                    'value' => $value->value ?? '',
                                ]);
                            })
                            ->form(function () use ($field) {
                                if ($field->field_type === 'rich-editor') {
                                    return [
                                        RichEditor::make('value')
                                            ->label($field->name),
                                    ];
                                }
                            })
                            ->icon(fn () => match ($field->field_type) {
                                'builder' => 'heroicon-o-squares-plus',
                                'rich-editor' => 'heroicon-o-code-bracket',
                                default => 'heroicon-o-pencil-square',
                            })
                            ->color('gray')
                            ->action(function (Content $record, array $data, Action $action) use ($field): void {
                                $record->values->where('field_ulid', $field->ulid)->first()->update([
                                    'value' => $data['value'],
                                ]);
                                $action->success();
                            })
                            ->button()
                    )
                    ->toArray(),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('published')
                    ->label(__(''))
                    ->width(1)
                    ->icon(fn (string $state): string => match ($state) {
                        'draft' => 'heroicon-o-pencil-square',
                        'expired' => 'heroicon-o-x-mark-circle',
                        'published' => 'heroicon-o-check-circle',
                        'scheduled' => 'heroicon-o-calendar-days',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'expired' => 'danger',
                        'published' => 'success',
                        'scheduled' => 'info',
                        default => 'gray',
                    })
                    ->size(IconColumn\IconColumnSize::Medium)
                    ->getStateUsing(fn (Content $record) => $record->published_at ? 'published' : 'draft'),

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

                        Badge::make('pin')
                            ->label(fn (Content $record) => $record->pin ? __('Pinned') : '')
                            ->color('info')
                            ->visible(fn (Content $record) => (bool) $record->pin),
                    ]),

                ImageColumn::make('authors')
                    ->circular()
                    ->stacked()
                    ->ring(2)
                    ->getStateUsing(fn (Content $record) => collect($record->authors)->map(fn (User $user) => Filament::getUserAvatarUrl($user))->toArray())
                    ->limit(3),

                ImageColumn::make('language_code')
                    ->label(__('Language'))
                    ->width(1)
                    ->getStateUsing(fn (Content $record) => explode('-', $record->language_code)[0])
                    ->view('backstage::filament.tables.columns.language-flag-column')
                    ->visible(fn () => Language::active()->count() > 1),

                ViewColumn::make('country_code')
                    ->label(__('Country'))
                    ->width(1)
                    ->getStateUsing(fn (Content $record) => strtolower(explode('-', $record->language_code)[1]) ?? __('Worldwide'))
                    ->view('backstage::filament.tables.columns.country-flag-column')
                    ->visible(fn () => Language::active()->where('code', 'LIKE', '%-%')->distinct(DB::raw('SUBSTRING_INDEX(code, "-", -1)'))->count() > 1),

                TextColumn::make('edited_at')
                    ->since()
                    ->alignEnd()
                    ->sortable(),
            ])
            ->modifyQueryUsing(
                fn (EloquentBuilder $query) => $query->with('ancestors', 'authors', 'type')
            )
            ->defaultSort(self::$type->sort_column ?? 'position', self::$type->sort_direction ?? 'desc')
            ->filters([
                Filter::make('locale')
                    ->form([
                        Select::make('language_code')
                            ->label(__('Language'))
                            ->columnSpanFull()
                            ->placeholder(__('Select Language'))
                            ->options(
                                Language::active()
                                    ->get()
                                    ->sort()
                                    ->groupBy(function ($language) {
                                        return Str::contains($language->code, '-') ? localized_country_name($language->code) : __('Worldwide');
                                    })
                                    ->mapWithKeys(fn ($languages, $countryName) => [
                                        $countryName => $languages->mapWithKeys(fn ($language) => [
                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ' . localized_language_name($language->code) . ' (' . $countryName . ')',
                                        ])->toArray(),
                                    ])
                            )
                            ->live()
                            ->allowHtml(),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query->when($data['language_code'] ?? null, function ($query, $languageCode) {
                            return $query->where('language_code', $languageCode);
                        });
                    })
                    ->visible(fn () => Language::active()->count() > 1),
                SelectFilter::make('type_slug')
                    ->label(__('Type'))
                    ->native(false)
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('type', 'name'),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->native(false)
                    ->options([
                        'draft' => __('Draft'),
                        'expired' => __('Expired'),
                        'published' => __('Published'),
                        'scheduled' => __('Scheduled'),
                    ])
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('public')
                    ->placeholder('Public and private')
                    ->label(__('Public'))
                    ->native(false),
                TernaryFilter::make('pin')
                    ->label(__('Pinned'))
                    ->placeholder('Pinned and unpinned')
                    ->native(false),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->label(__('Tags'))
                    ->native(false)
                    ->preload()
                    ->multiple(),
                TernaryFilter::make('parent_ulid')
                    ->nullable()
                    ->label('Parent')
                    ->trueLabel('Has parent')
                    ->falseLabel('No parent')
                    ->queries(
                        true: function (EloquentBuilder $query): EloquentBuilder {
                            return $query->whereNotNull('parent_ulid');
                        },
                        false: function (EloquentBuilder $query): EloquentBuilder {
                            return $query->whereNull('parent_ulid');
                        },
                    ),
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
                    ->label(__('Filter'))
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
