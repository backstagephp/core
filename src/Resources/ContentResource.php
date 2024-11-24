<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
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
use Vormkracht10\Backstage\Fields\RichEditor;
use Vormkracht10\Backstage\Fields\Select;
use Vormkracht10\Backstage\Fields\Text;
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
        $type = Type::firstWhere('slug', ($form->getLivewire()->data['type_slug'] ?? $form->getRecord()->type_slug));

        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Content')
                            ->schema([
                                Grid::make([
                                    'default' => 12,
                                ])->schema([
                                    Hidden::make('type_slug')
                                        ->default($type->slug),
                                ]),
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('title', Str::title($state));
                                        $set('slug', Str::slug($state));
                                    }),
                                //     Section::make('body')
                                //         ->heading(__('Body'))
                                //         ->schema([
                                //             Builder::make('body')
                                //                 ->hiddenLabel()
                                //                 ->columnSpanFull()
                                //                 ->blocks([
                                //                     Builder\Block::make('heading')
                                //                         ->icon('heroicon-o-h1')
                                //                         ->schema([
                                //                             TextInput::make('content')
                                //                                 ->label('Heading')
                                //                                 ->required(),
                                //                             Select::make('level')
                                //                                 ->options([
                                //                                     'h1' => 'Heading 1',
                                //                                     'h2' => 'Heading 2',
                                //                                     'h3' => 'Heading 3',
                                //                                     'h4' => 'Heading 4',
                                //                                     'h5' => 'Heading 5',
                                //                                     'h6' => 'Heading 6',
                                //                                 ])
                                //                                 ->required(),
                                //                         ])
                                //                         ->columns(2),
                                //                     Builder\Block::make('columns')
                                //                         ->icon('heroicon-o-view-columns')
                                //                         ->schema([
                                //                             Builder::make('body')
                                //                                 ->hiddenLabel()
                                //                                 ->columnSpanFull()
                                //                                 ->blocks([
                                //                                     Builder\Block::make('heading')
                                //                                         ->schema([
                                //                                             TextInput::make('content')
                                //                                                 ->label('Heading')
                                //                                                 ->required(),
                                //                                             Select::make('level')
                                //                                                 ->options([
                                //                                                     'h1' => 'Heading 1',
                                //                                                     'h2' => 'Heading 2',
                                //                                                     'h3' => 'Heading 3',
                                //                                                     'h4' => 'Heading 4',
                                //                                                     'h5' => 'Heading 5',
                                //                                                     'h6' => 'Heading 6',
                                //                                                 ])
                                //                                                 ->required(),
                                //                                         ])
                                //                                         ->columns(2),
                                //                                     Builder\Block::make('form')
                                //                                         ->icon('heroicon-o-inbox-arrow-down')
                                //                                         ->schema([
                                //                                             Select::make('form')
                                //                                                 ->label('Form')
                                //                                                 ->options([
                                //                                                     'contact' => 'Contact',
                                //                                                     'newsletter' => 'Newsletter',
                                //                                                 ])
                                //                                                 ->required(),
                                //                                         ]),
                                //                                     Builder\Block::make('paragraph')
                                //                                         ->schema([
                                //                                             RichEditor::make('content')
                                //                                                 ->label('Paragraph')
                                //                                                 ->required(),
                                //                                         ]),
                                //                                     Builder\Block::make('image')
                                //                                         ->icon('heroicon-o-photo')
                                //                                         ->schema([
                                //                                             FileUpload::make('url')
                                //                                                 ->label('Image')
                                //                                                 ->image()
                                //                                                 ->required(),
                                //                                             TextInput::make('alt')
                                //                                                 ->label('Alt text')
                                //                                                 ->required(),
                                //                                         ]),
                                //                                 ])->cloneable()
                                //                                 ->addActionLabel(__('Add a new block'))
                                //                                 ->collapsible()
                                //                                 ->collapsed()
                                //                                 ->reorderableWithButtons(),
                                //                         ]),
                                //                     Builder\Block::make('image')
                                //                         ->icon('heroicon-o-photo')
                                //                         ->schema([
                                //                             FileUpload::make('url')
                                //                                 ->label('Image')
                                //                                 ->image()
                                //                                 ->required(),
                                //                             TextInput::make('alt')
                                //                                 ->label('Alt text')
                                //                                 ->required(),
                                //                         ]),
                                //                     Builder\Block::make('form')
                                //                         ->icon('heroicon-o-inbox-arrow-down')
                                //                         ->schema([
                                //                             Select::make('form')
                                //                                 ->label('Form')
                                //                                 ->options([
                                //                                     'contact' => 'Contact',
                                //                                     'newsletter' => 'Newsletter',
                                //                                 ])
                                //                                 ->required(),
                                //                         ]),
                                //                     Builder\Block::make('video')
                                //                         ->icon('heroicon-o-video-camera')
                                //                         ->schema([]),
                                //                     Builder\Block::make('slider')
                                //                         ->icon('heroicon-o-square-3-stack-3d')
                                //                         ->schema([]),
                                //                     Builder\Block::make('overview')
                                //                         ->icon('heroicon-o-squares-2x2')
                                //                         ->schema([
                                //                             Select::make('type')
                                //                                 ->label('Type')
                                //                                 ->options([
                                //                                     'Current' => [
                                //                                         'children' => 'Children',
                                //                                         'related' => 'Related',
                                //                                     ],
                                //                                     'Other' => [
                                //                                         'blog' => 'Blog',
                                //                                         'page' => 'Page',
                                //                                         'question' => 'Question',
                                //                                     ],
                                //                                 ])
                                //                                 ->required(),
                                //                             TextInput::make('items')
                                //                                 ->label('Items')
                                //                                 ->numeric()
                                //                                 ->default(3)
                                //                                 ->required(),
                                //                         ]),
                                //                     Builder\Block::make('content')
                                //                         ->icon('heroicon-o-squares-2x2')
                                //                         ->schema([
                                //                             Select::make('content')
                                //                                 ->label('Content')
                                //                                 ->searchable()
                                //                                 ->options([
                                //                                     'test' => 'Dit is een review',
                                //                                 ])
                                //                                 ->createOptionForm([
                                //                                     TextInput::make('name')
                                //                                         ->required(),
                                //                                     TextInput::make('email')
                                //                                         ->required()
                                //                                         ->email(),
                                //                                 ])
                                //                                 ->required(),
                                //                         ]),
                                //                     Builder\Block::make('filter')
                                //                         ->icon('heroicon-o-funnel')
                                //                         ->schema([]),
                                //                 ])->cloneable()->blockPickerColumns(3)
                                //                 ->addActionLabel(__('Add a new block'))
                                //                 ->collapsible()
                                //                 ->collapsed()
                                //                 ->reorderableWithButtons(),
                                //         ]),
                            ]),
                        Tab::make($type->slug)
                            ->label(__($type->name))
                            ->schema([
                                Grid::make()
                                    ->columns(1)
                                    ->schema(self::getTypeInputs($type)),
                            ]),
                        Tab::make('Microdata')
                            ->schema([]),
                        Tab::make('SEO')
                            ->schema([]),
                        Tab::make('Revisions')
                            ->schema([
                                // ...
                            ]),
                        Tab::make('Redirects')
                            ->schema([
                                // ...
                            ]),
                        Tab::make('Statistics')
                            ->schema([
                                // ...
                            ]),
                        Tab::make('Options')
                            ->schema([
                                TextInput::make('title')
                                    ->columnSpanFull()
                                    ->required(),
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->required(),
                                Select::make('site_ulid')
                                    ->label(__('Site'))
                                    ->columnSpanFull()
                                    ->placeholder(__('Select Site'))
                                    ->prefixIcon('heroicon-o-link')
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

    public static function getTypeInputs(Type $type)
    {
        return $type->fields->map(function (Field $field) {
            $fieldName = 'fields.' . $field->ulid . '.value';

            return match ($field->field_type) {
                'text' => Text::make($fieldName, $field)
                    ->label($field->name),
                'checkbox' => Checkbox::make($fieldName, $field)
                    ->label($field->name),
                'textarea' => RichEditor::make($fieldName, $field)
                    ->label($field->name),
                'select' => Select::make($fieldName, $field)
                    ->label($field->name)
                    ->options($field->options),
                default => Text::make($fieldName, $field)
                    ->label($field->name),
            };
        })->toArray();
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
