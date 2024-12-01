<?php

namespace Vormkracht10\Backstage\Widgets;

use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Vormkracht10\Backstage\Models\Content;

class LatestContentWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Content::query()
                    ->where('site_ulid', Filament::getTenant()->getKey())
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->url(fn(Content $content) => route('filament.backstage.resources.content.edit', ['tenant' => Filament::getTenant(), 'record' => $content])),
            ])
            ->defaultPaginationPageOption(5);
    }
}
