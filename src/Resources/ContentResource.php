<?php

namespace Vormkracht10\Backstage\Resources;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
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
use Vormkracht10\Backstage\Models\Content;
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
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Content')
                            ->schema([
                                Hidden::make('content_type')
                                    ->default(request()->route()->parameter('type.slug')),
                                Select::make('parent_ulid')
                                    ->name(__('Parent'))
                                    ->options(
                                        Content::all()->pluck('name', 'ulid')->toArray()
                                    ),
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->live(debounce: 250)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', Str::slug($state));
                                        $set('path', '/' . Str::slug($state));
                                        $set('title', Str::title($state));
                                    }),
                                TextInput::make('title')
                                    ->columnSpanFull()
                                    ->required(),
                                // Section::make('body')
                                //     ->heading(__('Body'))
                                //     ->schema([
                                //         Builder::make('body')
                                //             ->hiddenLabel()
                                //             ->columnSpanFull()
                                //             ->blocks([
                                //                 Builder\Block::make('heading')
                                //                     ->icon('heroicon-o-h1')
                                //                     ->schema([
                                //                         TextInput::make('content')
                                //                             ->label('Heading')
                                //                             ->required(),
                                //                         Select::make('level')
                                //                             ->options([
                                //                                 'h1' => 'Heading 1',
                                //                                 'h2' => 'Heading 2',
                                //                                 'h3' => 'Heading 3',
                                //                                 'h4' => 'Heading 4',
                                //                                 'h5' => 'Heading 5',
                                //                                 'h6' => 'Heading 6',
                                //                             ])
                                //                             ->required(),
                                //                     ])
                                //                     ->columns(2),
                                //                 Builder\Block::make('columns')
                                //                     ->icon('heroicon-o-view-columns')
                                //                     ->schema([
                                //                         Builder::make('body')
                                //                             ->hiddenLabel()
                                //                             ->columnSpanFull()
                                //                             ->blocks([
                                //                                 Builder\Block::make('heading')
                                //                                     ->schema([
                                //                                         TextInput::make('content')
                                //                                             ->label('Heading')
                                //                                             ->required(),
                                //                                         Select::make('level')
                                //                                             ->options([
                                //                                                 'h1' => 'Heading 1',
                                //                                                 'h2' => 'Heading 2',
                                //                                                 'h3' => 'Heading 3',
                                //                                                 'h4' => 'Heading 4',
                                //                                                 'h5' => 'Heading 5',
                                //                                                 'h6' => 'Heading 6',
                                //                                             ])
                                //                                             ->required(),
                                //                                     ])
                                //                                     ->columns(2),
                                //                                 Builder\Block::make('form')
                                //                                     ->icon('heroicon-o-inbox-arrow-down')
                                //                                     ->schema([
                                //                                         Select::make('form')
                                //                                             ->label('Form')
                                //                                             ->options([
                                //                                                 'contact' => 'Contact',
                                //                                                 'newsletter' => 'Newsletter',
                                //                                             ])
                                //                                             ->required(),
                                //                                     ]),
                                //                                 Builder\Block::make('paragraph')
                                //                                     ->schema([
                                //                                         RichEditor::make('content')
                                //                                             ->label('Paragraph')
                                //                                             ->required(),
                                //                                     ]),
                                //                                 Builder\Block::make('image')
                                //                                     ->icon('heroicon-o-photo')
                                //                                     ->schema([
                                //                                         FileUpload::make('url')
                                //                                             ->label('Image')
                                //                                             ->image()
                                //                                             ->required(),
                                //                                         TextInput::make('alt')
                                //                                             ->label('Alt text')
                                //                                             ->required(),
                                //                                     ]),
                                //                             ])->cloneable()
                                //                             ->addActionLabel(__('Add a new block'))
                                //                             ->collapsible()
                                //                             ->collapsed()
                                //                             ->reorderableWithButtons(),
                                //                     ]),
                                //                 Builder\Block::make('image')
                                //                     ->icon('heroicon-o-photo')
                                //                     ->schema([
                                //                         FileUpload::make('url')
                                //                             ->label('Image')
                                //                             ->image()
                                //                             ->required(),
                                //                         TextInput::make('alt')
                                //                             ->label('Alt text')
                                //                             ->required(),
                                //                     ]),
                                //                 Builder\Block::make('form')
                                //                     ->icon('heroicon-o-inbox-arrow-down')
                                //                     ->schema([
                                //                         Select::make('form')
                                //                             ->label('Form')
                                //                             ->options([
                                //                                 'contact' => 'Contact',
                                //                                 'newsletter' => 'Newsletter',
                                //                             ])
                                //                             ->required(),
                                //                     ]),
                                //                 Builder\Block::make('video')
                                //                     ->icon('heroicon-o-video-camera')
                                //                     ->schema([]),
                                //                 Builder\Block::make('slider')
                                //                     ->icon('heroicon-o-square-3-stack-3d')
                                //                     ->schema([]),
                                //                 Builder\Block::make('overview')
                                //                     ->icon('heroicon-o-squares-2x2')
                                //                     ->schema([
                                //                         Select::make('type')
                                //                             ->label('Type')
                                //                             ->options([
                                //                                 'Current' => [
                                //                                     'children' => 'Children',
                                //                                     'related' => 'Related',
                                //                                 ],
                                //                                 'Other' => [
                                //                                     'blog' => 'Blog',
                                //                                     'page' => 'Page',
                                //                                     'question' => 'Question',
                                //                                 ],
                                //                             ])
                                //                             ->required(),
                                //                         TextInput::make('items')
                                //                             ->label('Items')
                                //                             ->numeric()
                                //                             ->default(3)
                                //                             ->required(),
                                //                     ]),
                                //                 Builder\Block::make('content')
                                //                     ->icon('heroicon-o-squares-2x2')
                                //                     ->schema([
                                //                         Select::make('content')
                                //                             ->label('Content')
                                //                             ->searchable()
                                //                             ->options([
                                //                                 'test' => 'Dit is een review',
                                //                             ])
                                //                             ->createOptionForm([
                                //                                 TextInput::make('name')
                                //                                     ->required(),
                                //                                 TextInput::make('email')
                                //                                     ->required()
                                //                                     ->email(),
                                //                             ])
                                //                             ->required(),
                                //                     ]),
                                //                 Builder\Block::make('filter')
                                //                     ->icon('heroicon-o-funnel')
                                //                     ->schema([]),
                                //             ])->cloneable()->blockPickerColumns(3)
                                //             ->addActionLabel(__('Add a new block'))
                                //             ->collapsible()
                                //             ->collapsed()
                                //             ->reorderableWithButtons(),
                                //     ]),
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
                                Hidden::make('author_id')->default(auth()->id()),
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->required(),
                                TextInput::make('path')
                                    ->columnSpanFull()
                                    ->required(),
                                Select::make('site_ulid')
                                    ->options(
                                        Site::all()->pluck('name', 'ulid')->toArray()
                                    )
                                    ->default(Site::where('default', true)->firstOrFail()->ulid),
                                Select::make('language_code')
                                    ->options(
                                        Language::all()->pluck('name', 'code')->toArray()
                                    )
                                    ->default(Language::where('default', true)->first()->code),
                                Select::make('country_code')
                                    ->options(
                                        Language::all()->pluck('name', 'code')->toArray()
                                    )
                                    ->default(Language::where('default', true)->first()->code),
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
            // FieldsRelationManager::class,
        ];
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
