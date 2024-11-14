<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\RichEditor as Input;
use Vormkracht10\Backstage\Enums\ToolbarButton;
use Vormkracht10\Backstage\Models\Field;

class RichEditor extends FieldBase implements FieldInterface
{
    public static function make(string $name, Field $field): Input
    {
        return Input::make($name)
            ->label($field->name)
            ->toolbarButtons($field->config['toolbarButtons'] ?? [])
            ->disableGrammarly($field->config['disableGrammarly'] ?? false)
            ->disableToolbarButtons($field->config['disableToolbarButtons'] ?? []);
    }

    public function getForm(): array
    {
        return [
            ...parent::getForm(),
            Forms\Components\Toggle::make('config.disableGrammarly')
                ->inline(false)
                ->label(__('Disable Grammarly')),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('config.toolbarButtons')
                        ->label(__('Toolbar buttons'))
                        ->default(['attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo'])
                        ->default(ToolbarButton::array()) // Not working in Filament yet.
                        ->multiple()
                        ->native(false)
                        ->options(ToolbarButton::array())
                        ->columnSpanFull(),
                    Forms\Components\Select::make('config.disableToolbarButtons')
                        ->label(__('Disabled toolbar buttons'))
                        ->multiple()
                        ->options(ToolbarButton::array())
                        ->columnSpanFull(),
                ]),
        ];
    }
}
