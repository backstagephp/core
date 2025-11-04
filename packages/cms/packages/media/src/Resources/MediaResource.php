<?php

namespace Backstage\Media\Resources;

use Backstage\Media\Components\Media;
use Backstage\Media\MediaPlugin;
use Backstage\Media\Resources\MediaResource\CreateMedia;
use Backstage\Media\Resources\MediaResource\EditMedia;
use Backstage\Media\Resources\MediaResource\ListMedia;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MediaResource extends Resource
{
    public static function getModel(): string
    {
        return config('backstage.media.model');
    }

    public static function isScopedToTenant(): bool
    {
        return config('backstage.media.is_tenant_aware') ?? static::$isScopedToTenant;
    }

    public static function getTenantOwnershipRelationshipName(): string
    {
        return config('backstage.media.tenant_ownership_relationship_name') ?? Filament::getTenantOwnershipRelationshipName();
    }

    public static function getModelLabel(): string
    {
        return MediaPlugin::get()->getLabel();
    }

    public static function getPluralModelLabel(): string
    {
        return MediaPlugin::get()->getPluralLabel();
    }

    public static function getNavigationLabel(): string
    {
        return MediaPlugin::get()->getNavigationLabel() ?? Str::title(static::getPluralModelLabel()) ?? Str::title(static::getModelLabel());
    }

    public static function getNavigationIcon(): string
    {
        return MediaPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return MediaPlugin::get()->getNavigationSort();
    }

    public static function getNavigationGroup(): ?string
    {
        return MediaPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationBadge(): ?string
    {
        if (! MediaPlugin::get()->getNavigationCountBadge()) {
            return null;
        }

        if (Filament::hasTenancy() && config('backstage.media.is_tenant_aware')) {
            return static::getEloquentQuery()
                ->where(config('backstage.media.tenant_relationship') . '_ulid', Filament::getTenant()->id)
                ->count();
        }

        return number_format(static::getModel()::count());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return MediaPlugin::get()->shouldRegisterNavigation();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Media::make()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_filename')
                    ->label(__('Original Filename'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('filename')
                    ->label(__('Filename'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('extension')
                    ->label(__('Extension'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('public')
                    ->boolean()
                    ->label(__('Public'))
                    ->sortable(),

            ])
            ->recordActions([
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip(__('Delete')),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(12)
            ->paginationPageOptions([6, 12, 24, 48, 'all'])
            ->recordUrl(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
