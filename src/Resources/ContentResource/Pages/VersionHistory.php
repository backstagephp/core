<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Fields\Models\Field;
use Backstage\Models\Content;
use Backstage\Models\Type;
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
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;

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
            ->recordActions([
                ViewAction::make()
                    ->label(__('View'))
                    ->modalHeading(fn (Version $record) => __('Version from :date', ['date' => $record->created_at->format('Y-m-d H:i:s')]))
                    ->schema(function (Version $record) {
                        $entries = [];
                        $fields = Field::where('model_type', 'type')
                            ->where('model_key', $record->content->type->slug)->get();
                        $entries[] = Infolists\Components\TextEntry::make('meta_tags')
                            ->label(__('Meta tags'))
                            ->formatState(function () use ($record) {
                                    return new HtmlString('
                                        <div class="text-gray-500 font-medium mb-2">' . __('Meta tags') . '</div>
                                        <div class="text-red-500 line-through">' . json_encode($record->content->meta_tags) . '</div>
                                        <div class="text-green-500">' . json_encode($record->data['meta_tags']) . '</div>');
                            });
                        foreach ($fields as $field) {
                            $orignalValue = $record->content->rawField($field->slug);
                            $newValue = $record->data['fields'][$field->ulid] ?? '';
                            $newValue = is_array($newValue) ? json_encode($newValue) : $newValue;
                            $entries[] = Infolists\Components\TextEntry::make($field->ulid)
                                ->label($field->name)
                                ->formatState(function () use ($orignalValue, $newValue, $field) {
                                        return new HtmlString('
                                            <div class="text-gray-500 font-medium mb-2">' . $field->name . '</div>
                                            <div class="text-red-500 line-through">' . $orignalValue . '</div>
                                            <div class="text-green-500">' . $newValue . '</div>');
                                });
                        }
                        return $entries;
                    })
                    ->modalFooterActions([
                        Action::make('restore')
                            ->label(__('Restore'))
                            ->icon('heroicon-o-arrow-path')
                            ->action(function (Version $record, Action $action): void {
                                $record->content->update([
                                    'meta_tags' => $record->data['meta_tags'] ?? [],
                                ]);
                                $record->content->values()->delete();
                                 foreach ($record->data['fields'] as $ulid => $value) {
                                     $record->content->values()->create([
                                         'field_ulid' => $ulid,
                                         'value' => json_encode($value),
                                     ]);
                                 }
                                 
                                 Notification::make()
                                     ->success()
                                     ->title(__('Version restored'))
                                     ->send();

                                $this->redirect($this->getResource()::getUrl('edit', ['record' => $record->content]));
                             }),
                         Action::make('cancel')
                             ->label(__('Cancel'))
                             ->close()
                             ->color('gray')
                    ]),
            ]);
    }
}
