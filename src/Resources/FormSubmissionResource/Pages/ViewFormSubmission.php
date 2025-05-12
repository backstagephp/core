<?php

namespace Backstage\Resources\FormSubmissionResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Backstage\Resources\FormSubmissionResource;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function getRecord(): Model
    {
        return parent::getRecord()->load(['values.field']);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('notes')
                ->label(__('Add Notes'))
                ->fillForm([
                    'notes' => $this->record->notes,
                ])
                ->form([
                    Textarea::make('notes')
                        ->label(__('Notes'))
                        ->rows(3)
                ])
                ->modalSubmitActionLabel(__('Save'))
                ->action(function (array $data) {
                    $this->record->update([
                        'notes' => $data['notes']
                    ]);

                    Notification::make()
                        ->title(__('Notes saved'))
                        ->success()
                        ->send();
                })
        ];
    }
}
