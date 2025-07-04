<?php

namespace Backstage\Resources\SettingResource\Pages;

use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Resources\SettingResource;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    use CanMapDynamicFields;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Setting')
                            ->label(__('Setting'))
                            ->schema([
                                Grid::make()
                                    ->columns(1)
                                    ->schema($this->resolveFormFields()),
                            ]),
                        Tab::make('Configure')
                            ->label(__('Configure'))
                            ->schema([
                                Grid::make()
                                    ->columns(2)
                                    ->schema(
                                        SettingResource::fields(),
                                    ),
                            ]),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = $this->mutateBeforeFill($data);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->mutateBeforeSave($data);

        return $data;
    }
}
