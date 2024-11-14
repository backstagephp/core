<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Vormkracht10\Backstage\Models\Field;

class Text extends FieldBase implements FieldInterface
{
    public static function make(string $name, Field $field): TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($field->name)
            ->placeholder($field->config['placeholder'] ?? null)
            ->prefix($field->config['prefix'] ?? null)
            ->prefixIcon($field->config['prefixIcon'] ?? null)
            ->prefixIconColor($field->config['prefixIconColor'] ?? null)
            ->suffix($field->config['suffix'] ?? null)
            ->suffixIcon($field->config['suffixIcon'] ?? null)
            ->suffixIconColor($field->config['suffixIconColor'] ?? null)
            ->mask($field->config['mask'] ?? null)
            ->minLength($field->config['minLength'] ?? null)
            ->maxLength($field->config['maxLength'] ?? null)
            ->type($field->config['type'] ?? 'text')
            ->step($field->config['step'] ?? null)
            ->inputMode($field->config['inputMode'] ?? null)
            ->telRegex($field->config['telRegex'] ?? null)
            ->revealable($field->config['revealable'] ?? false);
    }

    public function getForm(): array
    {
        return [
            ...parent::getForm(),
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
                                    Forms\Components\TextInput::make('config.prefixIconColor')
                                        ->type('color')
                                        ->label(__('Prefix color')),
                                    Forms\Components\TextInput::make('config.suffix')
                                        ->label(__('Suffix')),
                                    Forms\Components\TextInput::make('config.suffixIcon')
                                        ->placeholder('heroicon-m-')
                                        ->label(__('Suffix icon')),
                                    Forms\Components\TextInput::make('config.suffixIconColor')
                                        ->type('color')
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
                    Forms\Components\TextInput::make('config.revealable')
                        ->label(__('Revealable'))
                        ->visible(fn (Forms\Get $get): bool => $get('config.type') === 'password'),
                    Forms\Components\TextInput::make('config.telRegex')
                        ->label(__('Telephone regex'))
                        ->visible(fn (Forms\Get $get): bool => $get('config.type') === 'tel'),
                ]),
        ];
    }
}
