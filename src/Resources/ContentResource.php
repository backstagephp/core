<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Locale;
use Vormkracht10\Backstage\Fields\Builder;
use Vormkracht10\Backstage\Fields\RichEditor;
use Vormkracht10\Backstage\Fields\Select;
use Vormkracht10\Backstage\Fields\Text;
use Vormkracht10\Backstage\Fields\Textarea;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Resources\ContentResource\Pages;

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

    public static function form(Form $form): Form
    {
        self::$type = Type::firstWhere('slug', ($form->getLivewire()->data['type_slug'] ?? $form->getRecord()->type_slug));

        return $form
            ->schema([
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
                                            TextInput::make('meta_tags.keywords')
                                                ->label('Keywords')
                                                ->helperText('Meta keywords, altough not respected in search engines anymore, we also use it as focus keywords.')
                                                ->columnSpanFull(),
                                        ]),
                            ]),
                        Section::make()
                            ->columnSpan(4)
                            ->schema([
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->helperText('Unique string identifier for this content.')
                                    ->required(),
                                TextInput::make('path')
                                    ->columnSpanFull()
                                    ->helperText('Path to generate URL for this content.')
                                    ->required(),
                                Select::make('site_ulid')
                                    ->label(__('Site'))
                                    ->columnSpanFull()
                                    ->placeholder(__('Select Site'))
                                    ->prefixIcon('heroicon-o-window')
                                    ->options(Site::orderBy('default', 'desc')->orderBy('name', 'asc')->pluck('name', 'ulid'))
                                    ->default(Site::where('default', true)->first()?->ulid),
                                Select::make('country_code')
                                    ->label(__('Country'))
                                    ->columnSpanFull()
                                    ->placeholder(__('Select Country'))
                                    ->prefixIcon('heroicon-o-globe-europe-africa')
                                    ->options(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->get()->mapWithKeys(fn ($language) => [
                                        $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayRegion('-' . $language->code, app()->getLocale()),
                                    ])->sort())
                                    ->allowHtml()
                                    ->default(Language::whereActive(1)->whereNotNull('country_code')->distinct('country_code')->count() === 1 ? Language::whereActive(1)->whereNotNull('country_code')->first()->country_code : null),
                                Select::make('language_code')
                                    ->label(__('Language'))
                                    ->columnSpanFull()
                                    ->placeholder(__('Select Language'))
                                    ->prefixIcon('heroicon-o-language')
                                    ->options(
                                        Language::whereActive(1)->get()->mapWithKeys(fn ($language) => [
                                            $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(base_path('vendor/vormkracht10/backstage/resources/img/flags/' . $language->code . '.svg'))) . '" class="w-5 inline-block relative" style="top: -1px; margin-right: 3px;"> ' . Locale::getDisplayLanguage($language->code, app()->getLocale()),
                                        ])->sort()
                                    )
                                    ->allowHtml()
                                    ->default(Language::whereActive(1)->count() === 1 ? Language::whereActive(1)->first()->code : Language::whereActive(1)->where('default', true)->first()?->code),
                            ]),
                    ]),
            ]);
    }

    public static function getTypeInputs()
    {
        return self::$type->fields->map(function (Field $field) {
            $fieldName = 'fields.' . $field->ulid . '.value';

            $field->input = match ($field->field_type) {
                'text' => Text::make($fieldName, $field)
                    ->label($field->name),
                'checkbox' => Checkbox::make($fieldName, $field)
                    ->label($field->name),
                'rich-editor' => RichEditor::make($fieldName, $field)
                    ->label($field->name),
                'textarea' => Textarea::make($fieldName, $field)
                    ->label($field->name),
                'select' => Select::make($fieldName, $field)
                    ->label($field->name)
                    ->options($field->options),
                'builder' => Builder::make($fieldName, $field)
                    ->label($field->name),
                default => Text::make($fieldName, $field)
                    ->label($field->name),
            };

            return $field;
        })
        ->map(function($field) {
            if(self::$type->name_field == $field->slug) {
                $field->input->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $set('name', $state);
                        $set('meta_tags.title', $state);
                        $set('slug', Str::slug($state));
                        $set('path', Str::slug($state));
                    });
            }

            return $field;
        })
        ->map(fn($field) => $field->input)
        ->toArray();
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContent::route('/'),
            'create' => Pages\CreateContent::route('/create/{type}'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
