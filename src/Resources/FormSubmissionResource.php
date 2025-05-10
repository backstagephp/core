<?php

namespace Backstage\Resources;

use Backstage\Models\FormSubmission;
use Backstage\Resources\FormSubmissionResource\Pages;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    public static function getModelLabel(): string
    {
        return __('Submission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Submissions');
    }

    public static function getNavigationParentItem(): ?string
    {
        return 'Forms';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Form')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Textarea::make('notes')
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('form.name')
                    ->columnSpanFull()
                    ->label(__('Form'))
                    ->default('-'),
                TextEntry::make('notes')
                    ->columnSpanFull()
                    ->label(__('Notes'))
                    ->default('-'),
                TextEntry::make('content.name')
                    ->columnSpanFull()
                    ->label(__('Content'))
                    ->default('-')
                    ->state(function (FormSubmission $record): HtmlString {
                        return new HtmlString($record->content ? '<a href="' . $record->content->url . '" target="_blank">' . $record->content->name . '</a>' : '-');
                    }),
                TextEntry::make('language_code')
                    ->columnSpanFull()
                    ->label(__('Language'))
                    ->default('-'),
                TextEntry::make('country_code')
                    ->columnSpanFull()
                    ->label(__('Country'))
                    ->default('-'),
                TextEntry::make('ip_address')
                    ->columnSpanFull()
                    ->label(__('IP Address'))
                    ->default('-'),
                TextEntry::make('hostname')
                    ->columnSpanFull()
                    ->label(__('Hostname'))
                    ->default('-'),
                TextEntry::make('user_agent')
                    ->columnSpanFull()
                    ->label(__('User Agent'))
                    ->default('-'),
                TextEntry::make('submitted_at')
                    ->columnSpanFull()
                    ->label(__('Submitted At'))
                    ->default('-'),
                TextEntry::make('email_confirmed_at')
                    ->columnSpanFull()
                    ->label(__('Email confirmed At'))
                    ->default('-'),
                RepeatableEntry::make('values')
                    ->schema([
                        TextEntry::make('field.name')
                            ->state(function ($record) {
                                return $record->field?->name ?? '-';
                            }),
                        TextEntry::make('value'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('form_slug')
                    ->label('Form')
                    ->native(false)
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('form', 'name'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListFormSubmissions::route('/'),
            'view' => Pages\ViewFormSubmission::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['values.field']);
    }
}
