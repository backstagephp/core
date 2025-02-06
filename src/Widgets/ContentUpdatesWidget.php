<?php

namespace Vormkracht10\Backstage\Widgets;

use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Vormkracht10\Backstage\Models\Content;

class ContentUpdatesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recently updated content')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->url(fn (Content $content) => route('filament.backstage.resources.content.edit', ['tenant' => Filament::getTenant(), 'record' => $content])),
                ImageColumn::make('authors')
                    ->label('')
                    ->circular()
                    ->stacked()
                    ->ring(2)
                    ->alignRight()
                    ->getStateUsing(fn (Content $record) => collect($record->authors)->pluck('avatar_url')->toArray())
                    ->limit(1)
                    ->limitedRemainingText()
                    ->url(fn (Content $content) => route('filament.backstage.resources.content.edit', ['tenant' => Filament::getTenant(), 'record' => $content])),
                Tables\Columns\TextColumn::make('edited_at')
                    ->since()
                    ->alignRight()
                    ->url(fn (Content $content) => route('filament.backstage.resources.content.edit', ['tenant' => Filament::getTenant(), 'record' => $content])),
            ])
            ->query(
                Content::query()
                    ->with('authors')
                    ->where('site_ulid', Filament::getTenant()->getKey())
                    ->latest()
            )
            ->defaultPaginationPageOption(5);
    }
}
