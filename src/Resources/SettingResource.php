<?php

namespace Vormkracht10\Backstage\Resources;

use Builder\Block;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Spatie\SchemaOrg\Schema;
use Filament\Resources\Resource;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Setting;
use Vormkracht10\Backstage\Models\Language;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Vormkracht10\Backstage\Resources\SettingResource\Pages;
use Vormkracht10\Backstage\Resources\SettingResource\RelationManagers;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return __('Configure');
    }

    public static function getModelLabel(): string
    {
        return __('Setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Settings');
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
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required(),
                                Section::make('body')
                                    ->heading(__('Body'))
                                    ->schema([
                                        Builder::make('body')
                                            ->hiddenLabel()
                                            ->columnSpanFull()
                                            ->blocks([
                                                Builder\Block::make('heading')
                                                    ->schema([
                                                        TextInput::make('content')
                                                            ->label('Heading')
                                                            ->required(),
                                                        Select::make('level')
                                                            ->options([
                                                                'h1' => 'Heading 1',
                                                                'h2' => 'Heading 2',
                                                                'h3' => 'Heading 3',
                                                                'h4' => 'Heading 4',
                                                                'h5' => 'Heading 5',
                                                                'h6' => 'Heading 6',
                                                            ])
                                                            ->required(),
                                                    ])
                                                    ->columns(2),
                                                Builder\Block::make('paragraph')
                                                    ->schema([
                                                        RichEditor::make('content')
                                                            ->label('Paragraph')
                                                            ->required(),
                                                    ]),
                                                Builder\Block::make('image')
                                                    ->schema([
                                                        FileUpload::make('url')
                                                            ->label('Image')
                                                            ->image()
                                                            ->required(),
                                                        TextInput::make('alt')
                                                            ->label('Alt text')
                                                            ->required(),
                                                    ]),
                                            ])
                                            ->addActionLabel(__('Add a new block'))
                                            ->collapsible()
                                            ->collapsed()
                                            ->reorderableWithButtons()
                                    ])
                            ]),
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
                                // ...
                            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
