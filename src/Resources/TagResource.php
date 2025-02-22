<?php

namespace Backstage\Resources;

use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Backstage\Models\Tag;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Backstage\Resources\TagResource\Pages;
use Backstage\View\Components\Filament\Badge;
use Backstage\View\Components\Filament\BadgeableColumn;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static ?string $recordTitleAttribute = 'name';

    protected static ?string $tenantOwnershipRelationshipName = 'sites';

    public static function getModelLabel(): string
    {
        return __('Tag');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tags');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('name')
                    ->formatStateUsing(fn () => '')
                    ->searchable()
                    ->sortable()
                    ->separator('')
                    ->suffixBadges([
                        Badge::make('type')
                            ->label(fn (Tag $record) => '#' . $record->name)
                            ->color('gray'),
                    ])
                    ->url(fn (Tag $record) => route('filament.backstage.resources.content.index', [
                        'tenant' => Filament::getTenant(),
                        'tableFilters[tags][values]' => [$record->getKey()],
                    ])),
                TextColumn::make('content_count')
                    ->label('Times used')
                    ->counts('content')
                    ->url(fn (Tag $record) => route('filament.backstage.resources.content.index', [
                        'tenant' => Filament::getTenant(),
                        'tableFilters[tags][values]' => [$record->getKey()],
                    ])),
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }
}
