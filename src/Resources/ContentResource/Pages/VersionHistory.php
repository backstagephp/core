<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Models\Version;
use Backstage\Resources\ContentResource;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Contracts\Support\Htmlable;

class VersionHistory extends ManageRelatedRecords
{
    protected static string $resource = ContentResource::class;

    protected static string $relationship = 'versions';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-duplicate';

    public function getTitle(): string | Htmlable
    {
        $recordTitle = $this->getRecordTitle();

        $recordTitle = $recordTitle instanceof Htmlable ? $recordTitle->toHtml() : $recordTitle;

        return __('Revisions: :resource', [
            'resource' => $recordTitle,
        ]);
    }

    public function getBreadcrumb(): string
    {
        return __('Revisions');
    }

    public static function getNavigationLabel(): string
    {
        return __('Revisions');
    }

    public function getModelLabel(): string
    {
        return __('Revision');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Revisions');
    }

    public function form(Schema $schema): Schema
    {
        return ContentResource::form($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        $record = $this->getRecord();
        $fields = collect($record->data['fields'] ?? [])->map(function ($value, $field_ulid) {
            return "$field_ulid: " . $field_ulid;
        })->implode("\n");
// 
// TextEntry::make('data.meta_tags')
// ->label(__('Meta Tags')),
        return $schema
            ->schema([
                TextEntry::make('fields')
                    ->label(__('Fields'))
                    ->state($fields)
                    ->listWithLineBreaks(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('data.meta_tags.title')
                    ->label(__('Name'))
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make()
                    ->label(__('View'))
                    ->modalHeading(fn (Version $record) => __('Version from :date', ['date' => $record->created_at->format('Y-m-d H:i:s')]))
                    ->infolist($this->infolist(...)),
                Action::make('restore')
                    ->label(__('Restore'))
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (Version $record): void {
                        $content = $record->content;
                        
                        $content->update([
                            'meta_tags' => $record->data['meta_tags'] ?? [],
                        ]);

                        if (isset($record->data['fields'])) {
                            // Delete all existing field values
                            $content->values()->delete();
                            
                            // Create new field values
                            foreach ($record->data['fields'] as $ulid => $value) {
                                $content->values()->create([
                                    'field_ulid' => $ulid,
                                    'value' => json_encode($value),
                                ]);
                            }
                        }

                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $content]));
                    })
            ]);
    }
}
