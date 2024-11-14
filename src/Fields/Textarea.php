<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\Textarea as Input;
use Vormkracht10\Backstage\Models\Field;

class Textarea extends FieldBase implements FieldInterface
{
    public static function make(string $name, Field $field): Input
    {
        return Input::make($name)
            ->label($field->name)
            ->placeholder($field->config['placeholder'] ?? null)
            ->autosize($field->config['autosize'] ?? false)
            ->rows($field->config['rows'] ?? null)
            ->cols($field->config['cols'] ?? null)
            ->minLength($field->config['minLength'] ?? null)
            ->maxLength($field->config['maxLength'] ?? null)
            ->length($field->config['length'] ?? null);
    }

    public function getForm(): array
    {
        return [
            ...parent::getForm(),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('config.autosize')
                        ->default(false)
                        ->label(__('Auto size')),
                    Forms\Components\TextInput::make('config.rows')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Rows')),

                    Forms\Components\TextInput::make('config.cols')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Cols')),
                    Forms\Components\TextInput::make('config.minLength')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Minimum length')),
                    Forms\Components\TextInput::make('config.maxLength')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Maximum length')),
                    Forms\Components\TextInput::make('config.length')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Length')),
                    Forms\Components\TextInput::make('config.placeholder')
                        ->label(__('Placeholder')),
                ]),
        ];
    }
}
