<?php

namespace Vormkracht10\Backstage\Fields;

use Filament\Forms;
use Filament\Forms\Components\RichEditor as Input;
use Vormkracht10\Backstage\Enums\ToolbarButton;
use Vormkracht10\Backstage\Models\Field;

class RichEditor extends FieldBase implements FieldInterface
{
    public static function getDefaultConfig(): array
    {
        return [
            ...parent::getDefaultConfig(),
            'disableGrammarly' => false,
            'toolbarButtons' => ['attachFiles', 'blockquote', 'bold', 'bulletList', 'codeBlock', 'h2', 'h3', 'italic', 'link', 'orderedList', 'redo', 'strike', 'underline', 'undo'],
            'disableToolbarButtons' => [],
        ];
    }

    public static function make(string $name, ?Field $field = null): Input
    {
        return Input::make($name)
            ->label($field->name ?? null)
            ->required($field->config['required'] ?? self::getDefaultConfig()['required'])
            ->toolbarButtons($field->config['toolbarButtons'] ?? self::getDefaultConfig()['toolbarButtons'])
            ->disableGrammarly($field->config['disableGrammarly'] ?? self::getDefaultConfig()['disableGrammarly'])
            ->disableToolbarButtons($field->config['disableToolbarButtons'] ?? self::getDefaultConfig()['disableToolbarButtons']);
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
