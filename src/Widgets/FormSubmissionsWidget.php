<?php

namespace Backstage\Widgets;

use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Backstage\Models\FormSubmission;

class FormSubmissionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Newly submitted forms')
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
