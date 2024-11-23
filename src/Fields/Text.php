<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Support\Colors\Color;
use Vormkracht10\Backstage\Models\Field;

class Text extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'readOnly' => false,
            'autocapitalize' => 'none',
            'autocomplete' => null,
            'prefix' => null,
            'prefixIcon' => null,
            'prefixIconColor' => null,
            'suffix' => null,
            'suffixIcon' => null,
            'suffixIconColor' => null,
            'placeholder' => null,
            'mask' => null,
            'minLength' => null,
            'maxLength' => null,
            'type' => 'text',
            'step' => null,
            'inputMode' => null,
            'telRegex' => null,
            'revealable' => false,
        ];
    }

    public static function make(string $name, ?Field $field = null): TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($field->name ?? self::getDefaultConfig()['label'] ?? null)
            ->required($field->config['required'] ?? self::getDefaultConfig()['required'])
            ->readOnly($field->config['readOnly'] ?? self::getDefaultConfig()['readOnly'])
            ->placeholder($field->config['placeholder'] ?? self::getDefaultConfig()['placeholder'])
            ->prefix($field->config['prefix'] ?? self::getDefaultConfig()['prefix'])
            ->prefixIcon($field->config['prefixIcon'] ?? self::getDefaultConfig()['prefixIcon'])
            ->prefixIconColor(Color::hex($field->config['prefixIconColor'] ?? self::getDefaultConfig()['prefixIconColor']))
            ->suffix($field->config['suffix'] ?? self::getDefaultConfig()['suffix'])
            ->suffixIcon($field->config['suffixIcon'] ?? self::getDefaultConfig()['suffixIcon'])
            ->suffixIconColor($field->config['suffixIconColor'] ?? self::getDefaultConfig()['suffixIconColor'])
            ->mask($field->config['mask'] ?? self::getDefaultConfig()['mask'])
            ->minLength($field->config['minLength'] ?? self::getDefaultConfig()['minLength'])
            ->maxLength($field->config['maxLength'] ?? self::getDefaultConfig()['maxLength'])
            ->type($field->config['type'] ?? self::getDefaultConfig()['type'])
            ->step($field->config['step'] ?? self::getDefaultConfig()['step'])
            ->inputMode($field->config['inputMode'] ?? self::getDefaultConfig()['inputMode'])
            ->telRegex($field->config['telRegex'] ?? self::getDefaultConfig()['telRegex'])
            ->revealable($field->config['revealable'] ?? self::getDefaultConfig()['revealable']);
    }

    public function getForm(): array
    {
        return [
            ...parent::getForm(),
            Forms\Components\Toggle::make('config.readOnly')
                ->label(__('Read only'))
                ->inline(false),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('config.autocapitalize')
                        ->label(__('Autocapitalize'))
                        ->options([
                            'none' => __('None (off)'),
                            'sentences' => __('Sentences'),
                            'words' => __('Words'),
                            'characters' => __('Characters'),
                        ]),
                    Forms\Components\TextInput::make('config.autocomplete')
                        ->default(false)
                        ->label(__('Autocomplete')),
                    Forms\Components\Fieldset::make('Affixes')
                        ->columnSpanFull()
                        ->label(__('Affixes'))
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('config.prefix')
                                        ->label(__('Prefix')),
                                    Forms\Components\TextInput::make('config.prefixIcon')
                                        ->placeholder('heroicon-m-')
                                        ->label(__('Prefix icon')),
                                    Forms\Components\ColorPicker::make('config.prefixIconColor')
                                        ->label(__('Prefix color')),
                                    Forms\Components\TextInput::make('config.suffix')
                                        ->label(__('Suffix')),
                                    Forms\Components\TextInput::make('config.suffixIcon')
                                        ->placeholder('heroicon-m-')
                                        ->label(__('Suffix icon')),
                                    Forms\Components\ColorPicker::make('config.suffixIconColor')
                                        ->label(__('Suffix color')),
                                ]),
                        ]),
                    Forms\Components\TextInput::make('config.placeholder')
                        ->label(__('Placeholder')),
                    Forms\Components\TextInput::make('config.mask')
                        ->label(__('Mask')),
                    Forms\Components\TextInput::make('config.minLength')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Minimum length')),
                    Forms\Components\TextInput::make('config.maxLength')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Maximum length')),
                    Forms\Components\Select::make('config.type')
                        ->columnSpanFull()
                        ->label(__('Type'))
                        ->live(debounce: 250)
                        ->options([
                            'text' => __('Text'),
                            'password' => __('Password'),
                            'tel' => __('Telephone'),
                            'url' => __('URL'),
                            'email' => __('Email'),
                            'numeric' => __('Numeric'),
                            'integer' => __('Integer'),
                        ]),
                    Forms\Components\TextInput::make('config.step')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Step'))
                        ->visible(fn (Forms\Get $get): bool => $get('config.type') === 'numeric'),
                    Forms\Components\Select::make('config.inputMode')
                        ->label(__('Input mode'))
                        ->options([
                            'none' => __('None'),
                            'text' => __('Text'),
                            'decimal' => __('Decimal'),
                            'numeric' => __('Numeric'),
                            'tel' => __('Telephone'),
                            'search' => __('Search'),
                            'email' => __('Email'),
                            'url' => __('URL'),
                        ])
                        ->visible(fn (Forms\Get $get): bool => $get('config.type') === 'numeric'),
                    Forms\Components\Toggle::make('config.revealable')
                        ->label(__('Revealable'))
                        ->visible(fn (Forms\Get $get): bool => $get('config.type') === 'password'),
                    Forms\Components\TextInput::make('config.telRegex')
                        ->label(__('Telephone regex'))
                        ->visible(fn (Forms\Get $get): bool => $get('config.type') === 'tel'),
                ]),
        ];
    }
}
