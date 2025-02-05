<?php

namespace Vormkracht10\Backstage\Resources\BlockResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Resources\BlockResource;

class EditBlock extends EditRecord
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        $actions[] = Action::make('Create Component')
            ->requiresConfirmation()
            ->action(function (Block $record) {
                Artisan::call('make:component', ['name' => \Illuminate\Support\Str::studly($record->slug)]);

                if (file_exists(app_path('View/Components/' . \Illuminate\Support\Str::studly($this->record->slug) . '.php'))) {

                    // Replace __construct with params
                    $component = file_get_contents(app_path('View/Components/' . \Illuminate\Support\Str::studly($this->record->slug) . '.php'));
                    $params = '';
                    foreach ($this->record->fields as $field) {
                        $type = 'string';
                        $defaultValue = "''";
                        if ($field->config['multiple'] ?? false) {
                            $type = 'array';
                            $defaultValue = '[]';
                        }
                        $params .= 'public ' . $type . ' $' . $field->slug . '';
                        if (! $field->config['required']) {
                            $params .= ' = ' . $defaultValue . ', ';
                        } else {
                            $params .= ', ';
                        }
                    }
                    $params = rtrim($params, ', ');
                    $component = str_replace('__construct()', '__construct(' . $params . ')', $component);
                    file_put_contents(app_path('View/Components/' . \Illuminate\Support\Str::studly($this->record->slug) . '.php'), $component);
                }
                Notification::make()
                    ->title(__('Component created at :location', ['location' => app_path('View/Components/' . \Illuminate\Support\Str::studly($this->record->slug) . '.php')]))
                    ->success()
                    ->send();

                return $record;
            });

        $actions[] = Actions\DeleteAction::make();

        return $actions;
    }
}
