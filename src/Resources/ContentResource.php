<?php

namespace Backstage\Resources;

use BackedEnum;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Fields\Fields;
use Backstage\Fields\Fields\RichEditor;
use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Backstage\Models\Language;
use Backstage\Models\Tag;
use Backstage\Models\Type;
use Backstage\Models\User;
use Backstage\Resources\ContentResource\Pages\ContentTranslations;
use Backstage\Resources\ContentResource\Pages\CreateContent;
use Backstage\Resources\ContentResource\Pages\EditContent;
use Backstage\Resources\ContentResource\Pages\ListContent;
use Backstage\Resources\ContentResource\Pages\ListContentMetaTags;
use Backstage\Resources\ContentResource\Pages\ManageChildrenContent;
use Backstage\Resources\ContentResource\Pages\VersionHistory;
use Backstage\View\Components\Filament\Badge;
use Backstage\View\Components\Filament\BadgeableColumn;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
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
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentResource extends Resource
{
    use CanMapDynamicFields {
        resolveFormFields as private traitResolveFormFields;
        resolveFieldInput as private traitResolveFieldInput;
        mutateBeforeFill as private traitMutateBeforeFill;
    }

    protected static ?string $model = Content::class;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?Type $type = null;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        $record->load('type');

        return $record->name . ' (' . $record->type->name . ')';
    }

    public static function getModelLabel(): string
    {
        return __('Content');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Content');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'content';
    }

    public static function getNavigationItems(): array
    {
        // get route binding name of content
        $content = Content::where('ulid', request()->route()->parameter('record'))->first();

        $contentTypes = Type::orderBy('name')->get()->map(function (Type $type) use ($content) {
            return NavigationItem::make($type->slug)
                ->label($type->name_plural)
                ->parentItem(__('Content'))
                ->isActiveWhen(fn (NavigationItem $item) => in_array($type->slug, [request()->input('filters.type_slug.values.0'), $content?->type?->slug, request()->route()->parameter('type')?->slug ?? null]))
                ->url(route('filament.backstage.resources.content.index', [
                    'tenant' => Filament::getTenant(),
                    'filters[type_slug][values]' => [$type->slug],
                ]));
        })->toArray();

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn () => request()->routeIs(static::getRouteBaseName() . '.*') && ! request()->input('filters.type_slug.values.0') && ! request()->is('*/meta-tags'))
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

    private static function applyParentQueryFilters(EloquentBuilder $query, $form): EloquentBuilder
    {
        if (self::$type?->parent_filters) {
            $query->where(function ($query) {
                foreach (self::$type->parent_filters as $filter) {
                    $query->where(
                        column: $filter['column'],
                        operator: $filter['operator'],
                        value: $filter['value']
                    );
                }
            });
        }

        $languageCode = $form->getLivewire()->data['language_code'] ?? null;

        if (! $languageCode) {
            // If no language is set in the form, get the default language
            $defaultLanguage = Language::active()->where('default', true)->first()
                ?? Language::active()->first();
            $languageCode = $defaultLanguage?->code;
        }

        if ($languageCode) {
            $query->where('language_code', $languageCode);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        self::$type = Type::firstWhere('slug', ($schema->getLivewire()->data['type_slug'] ?? $schema->getRecord()?->type_slug));

        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder(__('Name'))
                    ->columnSpanFull()
                    // ->withAI(hint: true)
                    // ->canTranslate(enabled: DB::table('languages')->where('active', true)->count() > 1, hint: true)
                    ->extraInputAttributes(['style' => 'font-size: 30px'])
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old, ?Content $record) {
                        $set('meta_tags.title', $state);
                        self::updatePathAndSlug($set, $get, $state, $record);
                    }),

                Grid::make(12)
                    ->columnSpanFull()
                    ->schema([
                        Tabs::make('Tabs')
                            ->columnSpan(8)
                            ->key(fn ($livewire) => 'main-tabs-' . ($livewire->formVersion ?? 0))
                            ->tabs([
                                Tab::make(self::$type->slug)
                                    ->icon('heroicon-o-' . self::$type->icon)
                                    ->label(__(self::$type->name))
                                    ->key(function ($livewire) {
                                        $v = $livewire->formVersion ?? 0;

                                        return 'tab-' . self::$type->slug . '-' . $v;
                                    })
                                    ->schema([
                                        Hidden::make('type_slug')
                                            ->default(self::$type->slug),
                                        Grid::make()
                                            ->columns(1)
                                            ->key(function ($livewire) {
                                                $v = $livewire->formVersion ?? 0;

                                                return 'dynamic-fields-grid-' . $v;
                                            })
                                            ->schema(function ($livewire) {
                                                return self::getTypeInputs($livewire);
                                            }),
                                    ]),
                                Tab::make('meta')
                                    ->label(__('Meta'))
                                    ->visible(fn (Get $get) => $get('public') === true)
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->schema([
                                        TextInput::make('path')
                                            ->columnSpanFull()
                                            ->rules(function (Get $get, $record) {
                                                if ($get('public') === false) {
                                                    return [];
                                                }

                                                return Rule::unique('content', 'path')
                                                    ->where('language_code', $get('language_code'))
                                                    ->ignore($record?->getKey(), $record?->getKeyName());
                                            })
                                            ->prefix(fn (Get $get) => parse_url(Content::getPathPrefixForLanguage($get('language_code') ?? Language::active()->first()?->code ?? 'en'), PHP_URL_PATH))
                                            ->formatStateUsing(fn (?Content $record) => ltrim($record->path ?? '', '/'))
                                            ->live()
                                            ->visible(fn (Get $get) => $get('public') === true)
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                                if ($get('public') === false) {
                                                    $set('path', '');
                                                }
                                            }),

                                        TextInput::make('meta_tags.title')
                                            ->label(__('Page Title'))
                                            ->withAI(function ($component, Get $get, Set $set) {
                                                $context = self::getContentContextForAI($get);
                                                $textFieldValues = self::getTextFieldValuesForAI($get);

                                                $requirements = [
                                                    'Maximum 60 characters',
                                                    'Generate the title in ' . $context['languageName'],
                                                    'Include the main keyword from the content name',
                                                    'Make it compelling and descriptive',
                                                    'Optimized for search engines',
                                                    'Return only the title, no additional text',
                                                ];

                                                return self::buildAIPrompt(
                                                    'Generate an SEO-friendly page title (maximum 60 characters) based on the following content:',
                                                    $context,
                                                    $textFieldValues,
                                                    $requirements
                                                );
                                            })
                                            ->columnSpanFull(),

                                        Textarea::make('meta_tags.description')
                                            ->label(__('Description'))
                                            ->helperText('Meta description for search engines.')
                                            ->withAI(function ($component, Get $get, Set $set) {
                                                $context = self::getContentContextForAI($get);
                                                $textFieldValues = self::getTextFieldValuesForAI($get);

                                                $requirements = [
                                                    'Maximum 160 characters',
                                                    'Generate the description in ' . $context['languageName'],
                                                    'Include the main keywords from the content',
                                                    'Make it compelling and encourage clicks',
                                                    'Optimized for search engines',
                                                    'Return only the description, no additional text',
                                                ];

                                                return self::buildAIPrompt(
                                                    'Generate an SEO-friendly meta description (maximum 160 characters) based on the following content:',
                                                    $context,
                                                    $textFieldValues,
                                                    $requirements
                                                );
                                            })
                                            ->columnSpanFull(),

                                        Select::make('meta_tags.robots')
                                            ->label(__('Robots'))
                                            ->options(['noindex' => __('Do not index this content (noindex)'), 'nofollow' => __('Do not follow links (nofollow)'), 'noarchive' => __('Do not archive this content (noarchive)'), 'nosnippet' => __('No description in search results (nosnippet)'), 'noodp' => __('Do not index this in Open Directory Project (noodp)')])
                                            ->multiple()
                                            ->default(function (?Content $record, Get $get) {
                                                if ($record && isset($record->meta_tags['robots']) && ! empty($record->meta_tags['robots'])) {
                                                    return $record->meta_tags['robots'];
                                                }

                                                $type = self::$type;
                                                if (! $type) {
                                                    $typeSlug = $get('type_slug') ?? $record?->type_slug;
                                                    if ($typeSlug) {
                                                        $type = Type::firstWhere('slug', $typeSlug);
                                                    }
                                                }

                                                return $type?->default_meta_tags_robots;
                                            })
                                            ->columnSpanFull(),

                                        TagsInput::make('meta_tags.keywords')
                                            ->label(__('Keywords'))
                                            ->formatStateUsing(fn ($state) => self::normalizeKeywordsState($state))
                                            ->dehydrateStateUsing(fn ($state) => self::normalizeKeywordsState($state))
                                            ->hintAction(
                                                function (Set $set, $component) {
                                                    return \Filament\Actions\Action::make('ai')
                                                        ->icon(config('backstage.ai.action.icon'))
                                                        ->label(config('backstage.ai.action.label'))
                                                        ->action(function (Get $get, Set $set) use ($component) {
                                                            try {
                                                                $context = self::getContentContextForAI($get);
                                                                $textFieldValues = self::getTextFieldValuesForAI($get);

                                                                $prompt = "Generate focus keywords based on the following content:\n\n";
                                                                $prompt .= 'Language: ' . $context['languageName'] . ' (' . $context['languageCode'] . ")\n";
                                                                $prompt .= 'Content Name/Title: ' . $context['contentName'] . "\n\n";

                                                                if (! empty($textFieldValues)) {
                                                                    $prompt .= "Text Fields:\n" . $textFieldValues . "\n\n";
                                                                }

                                                                $prompt .= "Requirements:\n";
                                                                $prompt .= "- Generate 5-10 relevant focus keywords\n";
                                                                $prompt .= '- Keywords should be in ' . $context['languageName'] . "\n";
                                                                $prompt .= "- Include the main keyword from the content name\n";
                                                                $prompt .= "- Include related terms and synonyms\n";
                                                                $prompt .= "- Keywords should be specific and relevant to the content\n";
                                                                $prompt .= "- Return ONLY a valid JSON array format, for example: [\"keyword1\", \"keyword2\", \"keyword3\"]\n";
                                                                $prompt .= "- Do not include any additional text, explanations, or formatting outside the JSON array\n";
                                                                $prompt .= '- Each keyword should be a single word or short phrase (2-3 words maximum)';

                                                                $model = key(config('backstage.ai.providers'));

                                                                $response = \Prism\Prism\Facades\Prism::text()
                                                                    ->using(config('backstage.ai.providers.' . $model), $model)
                                                                    ->withPrompt($prompt)
                                                                    ->asText();

                                                                // Process the response and convert to array before setting
                                                                $keywords = self::normalizeKeywordsState($response->text);
                                                                $set($component->getName(), $keywords);
                                                            } catch (\Prism\Prism\Exceptions\PrismException $exception) {
                                                                \Filament\Notifications\Notification::make()
                                                                    ->title('Text generation failed')
                                                                    ->body('Error: ' . $exception->getMessage())
                                                                    ->danger()
                                                                    ->send();
                                                            }
                                                        });
                                                }
                                            )
                                            ->helperText('Meta keywords are not used by search engines anymore, but use it to define focus keywords. Split keywords by comma or tab.')
                                            ->color('gray')
                                            ->columnSpanFull()
                                            ->reorderable()
                                            ->splitKeys(['Tab', ','])
                                            ->suggestions(Content::whereJsonLength('meta_tags->keywords', '>', 0)->orderBy('edited_at')->take(25)->get()->map(fn ($content) => $content->meta_tags['keywords'])->flatten()->filter()),
                                    ]),
                                Tab::make('open-graph')
                                    ->visible(fn (Get $get) => $get('public') === true)
                                    ->label(__('Open Graph'))
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Grid::make([
                                            'default' => 12,
                                        ])->schema([
                                            self::getFileUploadField(),

                                            Hidden::make('meta_tags.og_type')
                                                ->default('website')
                                                ->formatStateUsing(fn ($state) => $state ?? 'website'),

                                            Hidden::make('meta_tags.og_site_name')
                                                ->default(fn ($state) => Filament::getTenant()->name)
                                                ->formatStateUsing(fn ($state) => $state ?? Filament::getTenant()->name),

                                            Hidden::make('meta_tags.og_url')
                                                ->formatStateUsing(fn ($state, ?Content $record) => $state ?? $record->url ?? null),

                                            Hidden::make('meta_tags.og_locale')
                                                ->formatStateUsing(fn ($state, ?Content $record) => $state ?? $record->language_code ?? null),
                                        ]),
                                    ]),
                                Tab::make('template')
                                    ->label(__('Template'))
                                    ->icon('heroicon-o-clipboard')
                                    ->schema([
                                        TextInput::make('view')
                                            ->label(__('View'))
                                            ->columnSpanFull()
                                            ->helperText('View to use for rendering this content. E.g. "content.search" or "overview".'),
                                    ]),
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
                                            ->relationship(
                                                relationship: 'parent',
                                                titleAttribute: 'name',
                                                parentAttribute: 'parent_ulid',
                                                modifyQueryUsing: function (EloquentBuilder $query, $record) use ($schema) {
                                                    $query = self::applyParentQueryFilters($query, $schema);

                                                    // Select all necessary columns
                                                    $query->select([
                                                        'ulid',
                                                        'name',
                                                        'parent_ulid',
                                                        'language_code',
                                                        'type_slug',
                                                        'created_at',
                                                        'updated_at',
                                                    ]);

                                                    return $query;
                                                },
                                            )
                                            ->searchable()
                                            ->withCount()
                                            ->required(fn () => self::$type?->parent_required ?? false)
                                            ->rules([
                                                function (Get $get) use ($schema) {
                                                    $languageCode = $get('language_code')
                                                        ?? $schema->getLivewire()->data['language_code']
                                                        ?? $schema->getRecord()?->language_code;

                                                    $rule = Rule::exists('content', 'ulid');

                                                    if ($languageCode) {
                                                        $rule->where('language_code', $languageCode);
                                                    }

                                                    return $rule;
                                                },
                                            ])
                                            ->enableBranchNode()
                                            ->default(function (Get $get) use ($schema) {
                                                $query = Content::query();
                                                $query = self::applyParentQueryFilters($query, $schema);

                                                return $query->count() === 1 ? $query->first()->ulid : null;
                                            })
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?Content $record) {
                                                if ($state) {
                                                    $parent = Content::find($state);
                                                    $currentName = $get('name');

                                                    if ($parent->path && $currentName) {
                                                        self::updatePathAndSlug($set, $get, $currentName, $record);
                                                    }
                                                }
                                            })
                                            ->disabledOptions(fn ($record) => [$record?->getKey()]),

                                        Toggle::make('public')
                                            ->label(__('Public'))
                                            ->default(fn () => self::$type?->public ?? true)
                                            ->onIcon('heroicon-s-check')
                                            ->offIcon('heroicon-s-x-mark')
                                            ->inline(true)
                                            ->helperText(__('Make content publicly accessible on path.'))
                                            ->columnSpanFull()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, ?bool $state) {
                                                if ($state === false) {
                                                    $set('path', '');
                                                } elseif ($state === true && ! $get('path')) {
                                                    // If public is enabled and no path exists, generate one from name
                                                    $name = $get('name');
                                                    if ($name) {
                                                        self::updatePathAndSlug($set, $get, $name, null);
                                                    }
                                                }
                                            }),

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
                                            ->prefix(fn (Get $get) => parse_url(Content::getPathPrefixForLanguage($get('language_code') ?? Language::active()->first()?->code ?? 'en'), PHP_URL_PATH))
                                            ->formatStateUsing(fn (?Content $record) => ltrim($record->path ?? '', '/'))
                                            ->live(),

                                        Select::make('language_code')
                                            ->label(__('Language'))
                                            ->columnSpanFull()
                                            ->placeholder(__('Select Language'))
                                            ->options(
                                                Language::active()
                                                    ->get()
                                                    ->sort()
                                                    ->groupBy(function ($language) {
                                                        return Str::contains($language->code, ['-', '_']) ? localized_country_name($language->code) : __('Worldwide');
                                                    })
                                                    ->mapWithKeys(function ($languages, $countryName) {
                                                        $options = collect($languages)->mapWithKeys(function ($language) {
                                                            $flagPath = flag_path(explode('-', $language->code)[0]);

                                                            $languageIsPrimary = ! str($language->code)->contains(['-', '_']);

                                                            $flagIcon = str('')
                                                                ->when(file_exists($flagPath), function ($str) use ($flagPath) {
                                                                    $base64 = base64_encode(file_get_contents($flagPath));

                                                                    return $str->append('<img src="data:image/svg+xml;base64,' . $base64 . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ');
                                                                })
                                                                ->append(localized_language_name($language->code))
                                                                ->when(! $languageIsPrimary, function ($str) use ($language) {
                                                                    $parts = preg_split('/[-_]/', $language->code, 2);

                                                                    if (isset($parts[1])) {
                                                                        return $str->append(' (' . $parts[1] . ')');
                                                                    }

                                                                    return $str;

                                                                });

                                                            return [$language->code => $flagIcon];
                                                        });

                                                        return [$countryName => $options];
                                                    })
                                            )
                                            ->allowHtml()
                                            ->visible(fn () => Language::active()->count() > 1)
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                                $set('path', $get('path'));

                                                // Clear parent selection when language changes to prevent cross-language parent relationships
                                                $set('parent_ulid', null);
                                            }),

                                        TextInput::make('slug')
                                            ->columnSpanFull()
                                            ->helperText('Unique string identifier for this content.')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?Content $record) {
                                                $set('slug', Str::slug($state));
                                            })
                                            ->required(),

                                        Toggle::make('pin')
                                            ->label(__('Pin'))
                                            ->inline(true)
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
                                            ->default(function (Get $get, ?Content $record) {
                                                if ($record) {
                                                    return $record->published_at;
                                                }

                                                $type = self::$type;
                                                if (! $type) {
                                                    $type = Type::firstWhere('slug', ($get('type_slug') ?? $record?->type_slug));
                                                }

                                                if ($type && $type->published_at_empty_on_create) {
                                                    return null;
                                                }

                                                return now();
                                            })
                                            ->displayFormat('M j, Y - H:i')
                                            ->formatStateUsing(function (?Content $record, Get $get) {
                                                if ($record && $record->published_at) {
                                                    return $record->published_at;
                                                }

                                                if ($record) {
                                                    return null;
                                                }

                                                $type = self::$type;
                                                if (! $type) {
                                                    $type = Type::firstWhere('slug', $get('type_slug'));
                                                }

                                                if ($type && $type->published_at_empty_on_create) {
                                                    return null;
                                                }

                                                return now();
                                            })
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
                            ]),
                    ]),
            ]);
    }

    private static function resolveFormFields(mixed $record = null): array
    {
        $instance = new self;

        return $instance->traitResolveFormFields($record);
    }

    public static function setStaticType(?Type $type): void
    {
        self::$type = $type;
    }

    private static function resolveFieldInput(mixed $field, Collection $customFields, mixed $record = null, bool $isNested = false): ?object
    {
        $instance = new self;

        return $instance->traitResolveFieldInput($field, $customFields, $record, $isNested);
    }

    public static function getTypeInputs($livewire = null)
    {
        $v = $livewire->formVersion ?? 0;
        $typeSlug = self::$type?->slug ?? 'NULL';

        $fields = self::$type->fields;

        if ($fields instanceof \Illuminate\Database\Eloquent\Collection) {
            $fields = $fields->unique('ulid');
        } else {
            $fields = collect($fields)->unique('ulid');
        }

        // Group field models by their group property, filtering out the name field
        $groups = [];
        $fields->filter(fn ($field) => self::$type->name_field !== $field->slug)
            ->each(function ($field) use (&$groups) {
                $groups[$field->group ?? null][] = $field;
            });

        // Use trait method to wrap groups in components with custom field resolution
        $instance = new self;

        return $instance->wrapFieldGroupsInComponents($groups, function ($fieldModels) use ($v) {
            return collect($fieldModels)
                ->map(function ($field) use ($v) {
                    $resolvedField = self::resolveFieldInput($field, collect(Fields::getFields()), self::$type);
                    if ($resolvedField) {
                        if (method_exists($resolvedField, 'key')) {
                            $resolvedField->key($field->ulid . '-' . $v);
                        }
                        if (method_exists($resolvedField, 'id')) {
                            $resolvedField->id($field->ulid . '-' . $v);
                        }
                    }

                    return $resolvedField;
                })
                ->filter()
                ->values()
                ->all();
        }, $v);
    }

    private static function orderedIdColumn(): TextColumn
    {
        $showOrderedId = config(
            'backstage.cms.show_ordered_id_in_content_overview',
            config('backstage.cms.show_id_in_content_overview', false)
        );

        return TextColumn::make('ordered_id')
            ->label(__('ID'))
            ->sortable($showOrderedId)
            ->searchable($showOrderedId)
            ->visible(fn () => $showOrderedId);
    }

    public static function tableDatabase(Table $table, Type $type): Table
    {
        return $table
            ->columns([
                self::orderedIdColumn(),

                IconColumn::make('published')
                    ->label(null)
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
                    ->size(IconSize::Medium)
                    ->getStateUsing(fn (Content $record) => $record->published_at ? 'published' : 'draft'),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                ImageColumn::make('language_code')
                    ->label(null)
                    ->width(50)
                    ->getStateUsing(fn (Content $record) => explode('-', $record->language_code)[0])
                    ->view('backstage::filament.tables.columns.language-flag-column')
                    ->visible(fn () => Language::active()->count() > 1),

                ViewColumn::make('country_code')
                    ->label(null)
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
                                    dd($record);
                                    $record->values->where('field_ulid', $field->ulid)->first()->update([
                                        'value' => $state,
                                    ]);
                                });
                        }
                    })
                    ->toArray(),
            ])
            ->modifyQueryUsing(
                function (EloquentBuilder $query) use ($type) {
                    $query->with('ancestors', 'authors', 'type', 'values')->where('type_slug', $type->slug);

                    return $query;
                }
            )
            ->defaultSort($type->sort_column ?? 'position', $type->sort_direction ?? 'desc')
            ->recordActions([
                ...$type
                    ->fields
                    ->whereIn('field_type', ['rich-editor'])
                    ->map(
                        fn ($field) => Action::make($field->slug)
                            ->label(__('Edit :name', ['name' => $field->name]))
                            ->modal()
                            ->mountUsing(function (Schema $schema, Content $record) use ($field) {
                                $value = $record->values->where('field_ulid', $field->ulid)->first();

                                $schema->fill([
                                    'value' => $value->value ?? '',
                                ]);
                            })
                            ->schema(function () use ($field) {
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
                    ),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable(['slug'])
            ->columns([
                self::orderedIdColumn(),

                BadgeableColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->separator('')
                    ->description(
                        description: fn (Content $record) => $record->ancestors?->reverse()->implode('name', ' / ') ?? null,
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
                    ->width(1)
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

                IconColumn::make('published')
                    ->label(null)
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
                    ->alignEnd()
                    ->tooltip(fn (string $state): string => __($state))
                    ->size(IconSize::Medium)
                    ->getStateUsing(fn (Content $record) => $record->published_at ? 'published' : 'draft'),

                TextColumn::make('edited_at')
                    ->label(__('Last updated'))
                    ->since()
                    ->width(1)
                    ->alignEnd()
                    ->sortable(),
            ])
            ->modifyQueryUsing(
                function (EloquentBuilder $query) {
                    $query->with('ancestors', 'authors', 'type');

                    return $query;
                }
            )
            ->defaultSort(self::$type->sort_column ?? 'position', self::$type->sort_direction ?? 'desc')
            ->filters([
                SelectFilter::make('language_code')
                    ->label(__('Language'))
                    ->columnSpanFull()
                    ->placeholder(__('Select Language'))
                    ->searchable()
                    ->options(
                        Language::active()
                            ->get()
                            ->sort()
                            ->groupBy(function ($language) {
                                return Str::contains($language->code, '-') ? localized_country_name($language->code) : __('Worldwide');
                            })
                            ->mapWithKeys(fn ($languages, $countryName) => [
                                $countryName => $languages->mapWithKeys(fn ($language) => [
                                    $language->code => localized_language_name($language->code) . ' (' . $countryName . ')',
                                ])->toArray(),
                            ])
                    )
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query->when($data['value'] ?? null, function ($query, $languageCode) {
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
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query->where(function (EloquentBuilder $query) use ($data) {
                            $values = $data['values'] ?? $data['value'] ?? [];
                            if (! is_array($values)) {
                                $values = [$values];
                            }

                            foreach ($values as $value) {
                                $query->orWhere(function (EloquentBuilder $query) use ($value) {
                                    match ($value) {
                                        'draft' => $query->draft(),
                                        'published' => $query->published(),
                                        'scheduled' => $query->scheduled(),
                                        'expired' => $query->expired(),
                                        default => null,
                                    };
                                });
                            }
                        });
                    })
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
                    ->schema([
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
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, Content $record) {
                        $values = $record->getFormattedFieldValues();

                        $record->values = $values;

                        $data['values'] = $record->values;

                        $instance = new self;
                        $data = $instance->traitMutateBeforeFill($data);

                        return $data;
                    })
                    ->using(function (Content $record, array $data): Content {
                        $valuesData = $data['values'] ?? [];
                        $tagsData = $data['tags'] ?? [];

                        unset($data['tags'], $data['values']);

                        $data['edited_at'] = now();

                        $record->update($data);

                        $tags = collect($tagsData)
                            ->filter(fn ($tag) => filled($tag))
                            ->map(fn (string $tag) => Tag::firstOrCreate([
                                'name' => $tag,
                                'slug' => Str::slug($tag),
                            ]))
                            ->each(fn (Tag $tag) => $tag->sites()->syncWithoutDetaching($record->site));

                        $record->tags()->sync($tags->pluck('ulid')->toArray());

                        if (! empty($valuesData)) {
                            foreach ($valuesData as $fieldUlid => $value) {
                                $record->values()->updateOrCreate([
                                    'content_ulid' => $record->getKey(),
                                    'field_ulid' => $fieldUlid,
                                ], [
                                    'value' => is_array($value) ? json_encode($value) : $value,
                                ]);
                            }
                        }

                        $record->authors()->syncWithoutDetaching(auth()->id());

                        return $record;
                    }),
            ])->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label(__('Filter'))
                    ->slideOver(),
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),

                BulkActionGroup::make([
                    BulkAction::make('translate_all')
                        ->visible(fn () => Language::query()->count() > 0)
                        ->icon(fn (): BackedEnum => Heroicon::OutlinedLanguage)
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $languages = Language::all();

                            $records->each(function (Content $record) use ($languages) {
                                foreach ($languages as $language) {
                                    $record->translate($language);
                                }
                            });

                        }),

                    ...Language::all()
                        ->map(function (Language $language) {
                            return BulkAction::make($language->code)
                                ->label($language->name)
                                ->icon(function () use ($language) {
                                    $flagCode = explode('-', $language->code)[0];
                                    $flagPath = flag_path($flagCode);

                                    return file_exists($flagPath)
                                        ? 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($flagPath))
                                        : null;
                                })
                                ->requiresConfirmation()
                                ->action(function (Collection $records) use ($language) {
                                    $records->each(function (Content $record) use ($language) {
                                        $record->translate($language);
                                    });
                                });
                        }),
                ])
                    ->icon(fn (): BackedEnum => Heroicon::OutlinedLanguage)
                    ->label(fn (): string => __('Translate')),
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
            'versions' => VersionHistory::route('/{record}/versions'),
            'translations' => ContentTranslations::route('/{record}/translations'),
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditContent::class,
            ManageChildrenContent::class,
            VersionHistory::class,
            ContentTranslations::class,
        ]);
    }

    private static function updatePathAndSlug(Set $set, Get $get, ?string $state, ?Content $record): void
    {
        // Only update path if content is public
        if ($get('public') === true) {
            $parentPath = $get('parent_ulid') ? Content::find($get('parent_ulid'))->path : '';
            $slug = Str::slug($state);
            $path = $parentPath ? trim($parentPath, '/') . '/' . $slug : $slug;
            $set('path', ltrim($path, '/'));
        } else {
            // Clear path if not public
            $set('path', '');
        }

        $currentSlug = $get('slug');

        if (! $record || ! $record->slug || ! $currentSlug) {
            $set('slug', Str::slug($state));
        }
    }

    protected static function getFileUploadField()
    {
        $fieldClass = config('backstage.cms.default_file_upload_field', \Backstage\Fields\Fields\FileUpload::class);

        $field = $fieldClass::make('meta_tags.og_image')
            ->label(__('Open Graph Image'))
            ->image()
            ->imageEditorAspectRatios([
                '1.91:1',
            ])
            ->formatStateUsing(function (Get $get, ?Content $record, mixed $state) {
                $type = Type::find($get('type_slug'));

                if ($state) {
                    return $state;
                }

                if (! $type || ! $type->og_image_fields || empty($type->og_image_fields) || ! $record) {
                    return [];
                }

                $fieldValue = ContentFieldValue::whereIn('field_ulid', is_array($type->og_image_fields) ? $type->og_image_fields : [])
                    ->where('content_ulid', $record->ulid)
                    ->whereNotNull('value')
                    ->where('value', '!=', '')
                    ->first();

                if ($fieldValue) {
                    return $fieldValue->value;
                }

                return [];
            })
            ->columnSpanFull();

        if ($field instanceof \Backstage\Fields\Fields\FileUpload) {
            $field = $field->imageEditorViewportWidth('1200')
                ->imageEditorViewportHeight('630')
                ->imageEditor();
        }

        return $field;
    }

    /**
     * Extract plain text from rich editor content array.
     */
    private static function extractTextFromRichEditor(array $content): string
    {
        // Handle case where content might be wrapped in an array with 'type' => 'doc'
        if (isset($content['type']) && $content['type'] === 'doc' && isset($content['content']) && is_array($content['content'])) {
            $content = $content['content'];
        }

        if (! is_array($content)) {
            return '';
        }

        $textParts = [];

        foreach ($content as $item) {
            if (! is_array($item)) {
                continue;
            }

            // If item has 'text' key directly, use it
            if (isset($item['text'])) {
                $textParts[] = $item['text'];

                continue;
            }

            // If item has nested 'content', extract from it
            if (isset($item['content']) && is_array($item['content'])) {
                $itemText = self::extractTextFromNodes($item['content']);
                if (! empty($itemText)) {
                    $textParts[] = $itemText;
                }
            }
        }

        return implode(' ', $textParts);
    }

    private static function extractTextFromNodes(array $nodes): string
    {
        $textParts = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            if (isset($node['type']) && $node['type'] === 'text' && isset($node['text'])) {
                $textParts[] = $node['text'];
            } elseif (isset($node['content']) && is_array($node['content'])) {
                // Recursively extract from nested content
                $nestedText = self::extractTextFromNodes($node['content']);
                if (! empty($nestedText)) {
                    $textParts[] = $nestedText;
                }
            }
        }

        return implode(' ', $textParts);
    }

    private static function getContentContextForAI(Get $get): array
    {
        $contentName = $get('name') ?? '';
        $languageCode = $get('language_code') ?? Language::active()->where('default', true)->first()?->code ?? Language::active()->first()?->code;

        $language = Language::find($languageCode);
        $languageName = $language?->name ?? $languageCode;

        return [
            'contentName' => $contentName,
            'languageCode' => $languageCode,
            'languageName' => $languageName,
        ];
    }

    private static function getTextFieldValuesForAI(Get $get): string
    {
        $textFieldTypes = ['text', 'textarea', 'rich-editor', 'markdown-editor'];

        $textFields = collect(self::$type->fields ?? [])
            ->whereIn('field_type', $textFieldTypes)
            ->filter(fn ($field) => self::$type->name_field !== $field->slug);

        return $textFields
            ->map(function ($field) use ($get) {
                $value = $get('values.' . $field->ulid);
                if (empty($value)) {
                    return null;
                }

                // Handle rich-editor and markdown-editor which may contain JSON
                if (in_array($field->field_type, ['rich-editor', 'markdown-editor'])) {
                    if (is_string($value) && json_validate($value)) {
                        $decoded = json_decode($value, true);
                        if (is_array($decoded)) {
                            $value = self::extractTextFromRichEditor($decoded);
                        }
                    } elseif (is_array($value)) {
                        // Handle case where value is already an array (rich-editor format)
                        $value = self::extractTextFromRichEditor($value);
                    }
                }

                if (is_array($value)) {
                    $value = implode(' ', array_filter($value, fn ($item) => ! is_array($item)));
                }

                $value = (string) $value;

                $cleanValue = strip_tags($value);
                $cleanValue = preg_replace('/\s+/', ' ', trim($cleanValue));

                // Limit length to avoid overly long prompts
                if (strlen($cleanValue) > 2000) {
                    $cleanValue = substr($cleanValue, 0, 2000) . '...';
                }

                return $field->name . ': ' . $cleanValue;
            })
            ->filter()
            ->values()
            ->implode("\n");
    }

    private static function buildAIPrompt(string $intro, array $context, string $textFieldValues, array $requirements): string
    {
        $prompt = $intro . "\n\n";
        $prompt .= 'Language: ' . $context['languageName'] . ' (' . $context['languageCode'] . ")\n";
        $prompt .= 'Content Name/Title: ' . $context['contentName'] . "\n\n";

        if (! empty($textFieldValues)) {
            $prompt .= "Text Fields:\n" . $textFieldValues . "\n\n";
        }

        $prompt .= "Requirements:\n";
        foreach ($requirements as $requirement) {
            $prompt .= '- ' . $requirement . "\n";
        }

        return $prompt;
    }

    /**
     * Normalize keywords state to always be an array.
     * Handles string responses from AI by parsing JSON or splitting by comma.
     */
    private static function normalizeKeywordsState($state): array
    {
        if (is_array($state)) {
            // Filter out single character items (likely from character-by-character splitting)
            return array_values(array_filter($state, fn ($item) => is_string($item) && strlen($item) > 1));
        }

        if (! is_string($state) || empty(trim($state))) {
            return [];
        }

        // Try to parse as JSON first
        $decoded = json_decode($state, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($keyword) => ! empty($keyword) && strlen((string) $keyword) > 1));
        }

        // Otherwise split by comma
        $keywords = array_map('trim', explode(',', $state));
        $keywords = array_filter($keywords, fn ($keyword) => ! empty($keyword) && strlen($keyword) > 1);

        return array_values($keywords);
    }
}
