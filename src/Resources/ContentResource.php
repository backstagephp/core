<?php

namespace Backstage\Resources;

use Backstage\Fields\Builder;
use Backstage\Fields\Checkbox;
use Backstage\Fields\CheckboxList;
use Backstage\Fields\KeyValue;
use Backstage\Fields\RichEditor;
use Backstage\Fields\Select as FieldSelect;
use Backstage\Fields\Text;
use Backstage\Fields\Textarea;
use Backstage\Media\Fields\Media;
use Backstage\Models\Content;
use Backstage\Models\Field;
use Backstage\Models\Language;
use Backstage\Models\Tag;
use Backstage\Models\Type;
use Backstage\Resources\ContentResource\Pages;
use Backstage\View\Components\Filament\Badge;
use Backstage\View\Components\Filament\BadgeableColumn;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Locale;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

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
                    ->live(debounce: 250)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        $set('meta_tags.title', $state);
                        $set('slug', Str::slug($state));

                        if (blank($get('path'))) {
                            $set('path', Str::slug($state));
                        }
                    }),

                Grid::make('Content')
                    ->schema([
                        Select::make('parent_ulid')
                            ->placeholder('Parent')
                            ->hiddenLabel()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['xl' => 1])
                            ->rules([
                                Rule::exists('content', 'ulid')
                                    ->where('language_code', $form->getLivewire()->data['language_code'] ?? null),
                            ])
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (EloquentBuilder $query) use ($form) {
                                    $query->when($form->getLivewire()->data['language_code'] ?? null, function ($query, $languageCode) {
                                        $query->where('language_code', $languageCode);
                                    });
                                },
                                ignoreRecord: true,
                            ),

                        TextInput::make('path')
                            ->hiddenLabel()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(['xl' => 3])
                            ->prefix($form->getRecord()?->path_prefix ? $form->getRecord()->path_prefix : '/')
                            ->formatStateUsing(fn (?Content $record) => ltrim($record->path ?? '', '/')),
                    ])
                    ->columns(['xl' => 4]),

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
                                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' . $countryName . ')',
                                                        ])->toArray(),
                                                    ])
                                            )
                                            ->allowHtml()
                                            ->visible(fn () => Language::where('active', 1)->count() > 1),
                                        TextInput::make('slug')
                                            ->columnSpanFull()
                                            ->helperText('Unique string identifier for this content.')
                                            ->required(),
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
                                Tab::make('advanced')
                                    ->label('Advanced')
                                    ->schema([
                                        DateTimePicker::make('expired_at')
                                            ->label('Expiration date')
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

    public static function getTypeInputs()
    {
        return self::$type->fields->map(function (Field $field) {
            $fieldName = 'values.' . $field->ulid;

            $field->input = match ($field->field_type) {
                'text' => Text::make($fieldName, $field)
                    ->label($field->name),
                'checkbox' => Checkbox::make($fieldName, $field)
                    ->label($field->name),
                'checkbox-list' => CheckboxList::make($fieldName, $field)
                    ->label($field->name)
                    ->options($field->config['options']),
                'rich-editor' => RichEditor::make($fieldName, $field)
                    ->label($field->name),
                'textarea' => Textarea::make($fieldName, $field)
                    ->label($field->name),
                'select' => FieldSelect::make($fieldName, $field)
                    ->label($field->name),
                'builder' => Builder::make($fieldName, $field)
                    ->label($field->name),
                'media' => Media::make($fieldName, $field)
                    ->label($field->name),
                'key-value' => KeyValue::make($fieldName, $field),
                default => Text::make($fieldName, $field)
                    ->label($field->name),
            };

            return $field;
        })
            ->filter(fn ($field) => self::$type->name_field !== $field->slug)
            ->map(fn ($field) => $field->input)
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
                    ->view('backstage::filament.tables.columns.language-flag-column'),
                ViewColumn::make('country_code')
                    ->label('Country')
                    ->getStateUsing(fn (Content $record) => explode('-', $record->language_code)[1] ?? 'Worldwide')
                    ->view('backstage::filament.tables.columns.country-flag-column'),
                TextColumn::make('edited_at')
                    ->since()
                    ->alignEnd()
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (EloquentBuilder $query) => $query->with('ancestors', 'authors', 'type'))
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
                                        return explode('-', $language->code)[1] ?? 'Worldwide';
                                    })
                                    ->mapWithKeys(fn ($languages, $countryCode) => [
                                        $countryCode => $languages->mapWithKeys(fn ($language) => [
                                            $language->code . '-' . $countryCode => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/backstage/cms/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' . ($countryCode ? Locale::getDisplayRegion('-' . $countryCode, app()->getLocale()) : 'Worldwide') . ')',
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
                                $data['date_from'],
                                fn (EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate($data['date_column'], '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
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
            'index' => Pages\ListContent::route('/'),
            'create' => Pages\CreateContent::route('/create/{type}'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
            'meta_tags' => Pages\ListContentMetaTags::route('/meta-tags'),
        ];
    }
}
