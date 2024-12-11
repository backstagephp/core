<?php

namespace Vormkracht10\Backstage\Resources;

use Locale;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Vormkracht10\Backstage\Models\Tag;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\TextColumn;
use Vormkracht10\Backstage\Fields\Text;
use Vormkracht10\Backstage\Models\Type;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Vormkracht10\Backstage\Models\Field;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Vormkracht10\Backstage\Fields\Select;
use Vormkracht10\Backstage\Fields\Builder;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Fields\KeyValue;
use Vormkracht10\Backstage\Fields\Textarea;
use Vormkracht10\Backstage\Models\Language;
use Filament\Forms\Components\DateTimePicker;
use Vormkracht10\Backstage\Fields\RichEditor;
use Vormkracht10\Backstage\Fields\CheckboxList;
use Filament\Forms\Components\Select as SelectInput;
use Vormkracht10\MediaPicker\Components\MediaPicker;
use Vormkracht10\Backstage\View\Components\Filament\Badge;
use Vormkracht10\Backstage\Resources\ContentResource\Pages;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Vormkracht10\Backstage\View\Components\Filament\BadgeableColumn;

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
        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn() => request()->routeIs(static::getRouteBaseName() . '.*') && ! request()->input('tableFilters.type_slug.values.0') && ! request()->is('*/meta-tags'))
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->badgeTooltip(static::getNavigationBadgeTooltip())
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
        ];
    }

    public static function form(Form $form): Form
    {
        self::$type = Type::firstWhere('slug', ($form->getLivewire()->data['type_slug'] ?? $form->getRecord()->type_slug));

        return $form
            ->schema([
                TextInput::make('values.' . self::$type->fields->where('slug', self::$type->name_field)->first()->ulid)
                    ->hiddenLabel()
                    ->placeholder(self::$type->fields->where('slug', self::$type->name_field)->first()->name)
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'font-size: 30px'])
                    ->required()
                    ->live(debounce: 250)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        $set('name', $state);
                        $set('meta_tags.title', $state);
                        $set('slug', Str::slug($state));

                        if (blank($get('path'))) {
                            $set('path', Str::slug($state));
                        }
                    }),

                TextInput::make('path')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->prefix(config('app.url') . '/')
                    ->required(),

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
                                        Hidden::make('name'),
                                        Grid::make()
                                            ->columns(1)
                                            ->schema(self::getTypeInputs()),
                                        MediaPicker::make(),
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
                                            ->suggestions(Content::whereJsonLength('meta_tags->keywords', '>', 0)->orderBy('edited_at')->take(25)->get()->map(fn($content) => $content->meta_tags['keywords'])->flatten()->filter()),
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
                                            ->label('Publication date')
                                            ->date()
                                            ->prefixIcon('heroicon-o-calendar-days')
                                            ->default(now()->format('dd/mm/YYYY'))
                                            ->native(false)
                                            ->formatStateUsing(fn(?Content $record) => $record ? $record->published_at : now())
                                            ->columnSpanFull()
                                            ->helperText('Set a date in past or future to schedule publication.'),
                                        Select::make('language_code')
                                            ->label(__('Language'))
                                            ->columnSpanFull()
                                            ->placeholder(__('Select Language'))
                                            ->prefixIcon('heroicon-o-language')
                                            ->options(
                                                Language::where('active', 1)
                                                    ->get()
                                                    ->sort()
                                                    ->groupBy(function ($language) {
                                                        return Str::contains($language->code, '-') ? Locale::getDisplayRegion('-' . strtolower(explode('-', $language->code)[1]), app()->getLocale()) : 'Worldwide';
                                                    })
                                                    ->mapWithKeys(fn($languages, $countryName) => [
                                                        $countryName => $languages->mapWithKeys(fn($language) => [
                                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' . $countryName . ')',
                                                        ])->toArray(),
                                                    ])
                                            )
                                            ->allowHtml()
                                            ->visible(fn() => Language::where('active', 1)->count() > 1),
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
                                            ->formatStateUsing(fn($state, ?Content $record) => $state ?: $record?->tags->pluck('name')->toArray() ?: [])
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
                'select' => Select::make($fieldName, $field)
                    ->label($field->name)
                    ->options($field->config['options']),
                'builder' => Builder::make($fieldName, $field)
                    ->label($field->name),
                'media' => MediaPicker::make($fieldName, $field)
                    ->label($field->name),
                'key-value' => KeyValue::make($fieldName, $field),
                default => Text::make($fieldName, $field)
                    ->label($field->name),
            };

            return $field;
        })
            ->filter(fn($field) => self::$type->name_field !== $field->slug)
            ->map(fn($field) => $field->input)
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
                    ->suffixBadges([
                        Badge::make('type')
                            ->label(fn(Content $record) => $record->type->name)
                            ->color('gray'),
                    ]),
                ImageColumn::make('authors')
                    ->circular()
                    ->stacked()
                    ->ring(2)
                    ->getStateUsing(fn(Content $record) => collect($record->authors)->pluck('avatar_url')->toArray())
                    ->limit(3),
                TextColumn::make('edited_at')
                    ->since()
                    ->alignEnd()
                    ->sortable(),
            ])
            ->defaultSort('edited_at', 'desc')
            ->filters([
                Filter::make('locale')
                    ->form([
                        Select::make('language_code')
                            ->label(__('Language'))
                            ->columnSpanFull()
                            ->placeholder(__('Select Language'))
                            ->prefixIcon('heroicon-o-language')
                            ->options(
                                Language::where('active', 1)
                                    ->get()
                                    ->sort()
                                    ->groupBy(function ($language) {
                                        return explode('-', $language->code)[1] ?? 'Worldwide';
                                    })
                                    ->mapWithKeys(fn($languages, $countryCode) => [
                                        $countryCode => $languages->mapWithKeys(fn($language) => [
                                            $language->code . '-' . $countryCode => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . explode('-', $language->code)[0] . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage(explode('-', $language->code)[0], app()->getLocale()) . ' (' . ($language->country_code ? Locale::getDisplayRegion('-' . $language->country_code, app()->getLocale()) : 'Worldwide') . ')',
                                        ])->toArray(),
                                    ])
                            )
                            ->allowHtml(),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query->where('language_code', $data['language_code']);
                    })
                    ->visible(fn() => Language::where('active', 1)->count() > 1),
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
                                fn(EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate($data['date_column'], '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(EloquentBuilder $query, $date): EloquentBuilder => $query->whereDate($data['date_column'], '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersFormWidth('md')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])->filtersTriggerAction(
                fn(Action $action) => $action
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
