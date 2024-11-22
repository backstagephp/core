<?php

namespace Vormkracht10\Backstage\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Vormkracht10\Backstage\Models\Content;
use Filament\Widgets\TableWidget as BaseWidget;
use Vormkracht10\Backstage\Models\FormSubmission;

class LatestFormSubmissionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FormSubmission::query()
                    ->where('site_ulid', Filament::getTenant()->getKey())
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title'),
            ])
            ->defaultPaginationPageOption(5);
    }
}
