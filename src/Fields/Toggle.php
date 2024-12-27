<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Vormkracht10\Backstage\Models\Field;
use Vormkracht10\Backstage\Enums\ToggleColor;
use Filament\Forms\Components\Toggle as Input;
use Vormkracht10\Backstage\Contracts\FieldContract;

class Toggle extends FieldBase implements FieldContract
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'inline' => false,
            'accepted' => null,
            'declined' => null,
            'onColor' => ToggleColor::SUCCESS->value,
            'offColor' => ToggleColor::DANGER->value,
            'onIcon' => null,
            'offIcon' => null,
        ];
    }

    public static function make(string $name, Field $field): Input
    {
        $input = self::applyDefaultSettings(Input::make($name), $field);

        $input = $input->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->inline($field->config['inline'] ?? self::getDefaultConfig()['inline'])
            ->onColor($field->config['onColor'] ?? self::getDefaultConfig()['onColor'])
            ->offColor($field->config['offColor'] ?? self::getDefaultConfig()['offColor']);

        if ($field->config['accepted'] ?? self::getDefaultConfig()['accepted']) {
            $input->accepted($field->config['accepted']);
        }

        if ($field->config['declined'] ?? self::getDefaultConfig()['declined']) {
            $input->declined($field->config['declined']);
        }

        if ($field->config['onIcon'] ?? self::getDefaultConfig()['onIcon']) {
            $input->onIcon($field->config['onIcon']);
        }

        if ($field->config['offIcon'] ?? self::getDefaultConfig()['offIcon']) {
            $input->offIcon($field->config['offIcon']);
        }

        return $input;
    }

    public function getForm(): array
    {
        return [
            Forms\Components\Tabs::make()
                ->schema([
                    Forms\Components\Tabs\Tab::make('General')
                        ->label(__('General'))
                        ->schema([
                            ...parent::getForm(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Field specific')
                        ->label(__('Field specific'))
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Toggle::make('config.inline')
                                    ->label(__('Inline'))
                                    ->inline(false)
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('config.accepted')
                                    ->label(__('Accepted'))
                                    ->helperText(__('Requires the checkbox to be checked'))
                                    ->inline(false),
                                Forms\Components\Toggle::make('config.declined')
                                    ->label(__('Declined'))
                                    ->helperText(__('Requires the checkbox to be unchecked'))
                                    ->inline(false),
                                Forms\Components\Select::make('config.onColor')
                                    ->label(__('On color'))
                                    ->options(
                                        collect(ToggleColor::array())->map(function ($color) {
                                            return match (strtoupper($color)) {
                                                'DANGER' => '<div class="flex items-center gap-2"><span class="bg-danger-500 rounded-full h-3 w-3 inline-block"></span>Danger</div>',
                                                'GRAY' => '<div class="flex items-center gap-2"><span class="bg-gray-500 rounded-full h-3 w-3 inline-block"></span>Gray</div>',
                                                'INFO' => '<div class="flex items-center gap-2"><span class="bg-info-500 rounded-full h-3 w-3 inline-block"></span>Info</div>',
                                                'PRIMARY' => '<div class="flex items-center gap-2"><span class="bg-primary-500 rounded-full h-3 w-3 inline-block"></span>Primary</div>',
                                                'SUCCESS' => '<div class="flex items-center gap-2"><span class="bg-success-500 rounded-full h-3 w-3 inline-block"></span>Success</div>',
                                                'WARNING' => '<div class="flex items-center gap-2"><span class="bg-warning-500 rounded-full h-3 w-3 inline-block"></span>Warning</div>',
                                                default => $color
                                            };
                                        })
                                    )->allowHtml(),
                                Forms\Components\Select::make('config.offColor')
                                    ->label(__('Off color'))
                                    ->options(
                                        collect(ToggleColor::array())->map(function ($color) {
                                            return match (strtoupper($color)) {
                                                'DANGER' => '<div class="flex items-center gap-2"><span class="bg-danger-500 rounded-full h-3 w-3 inline-block"></span>Danger</div>',
                                                'GRAY' => '<div class="flex items-center gap-2"><span class="bg-gray-500 rounded-full h-3 w-3 inline-block"></span>Gray</div>',
                                                'INFO' => '<div class="flex items-center gap-2"><span class="bg-info-500 rounded-full h-3 w-3 inline-block"></span>Info</div>',
                                                'PRIMARY' => '<div class="flex items-center gap-2"><span class="bg-primary-500 rounded-full h-3 w-3 inline-block"></span>Primary</div>',
                                                'SUCCESS' => '<div class="flex items-center gap-2"><span class="bg-success-500 rounded-full h-3 w-3 inline-block"></span>Success</div>',
                                                'WARNING' => '<div class="flex items-center gap-2"><span class="bg-warning-500 rounded-full h-3 w-3 inline-block"></span>Warning</div>',
                                                default => $color
                                            };
                                        })
                                    )->allowHtml(),
                                Forms\Components\TextInput::make('config.onIcon')
                                    ->label(__('On icon')),
                                Forms\Components\TextInput::make('config.offIcon')
                                    ->label(__('Off icon')),
                            ]),
                        ]),
                ])->columnSpanFull(),
        ];
    }
}