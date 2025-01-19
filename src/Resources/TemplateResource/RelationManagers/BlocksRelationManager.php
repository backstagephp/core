<?php

namespace Vormkracht10\Backstage\Resources\TemplateResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Models\Block;
use Filament\Tables\Actions\AttachAction;
use Vormkracht10\Fields\Concerns\HasFieldsMapper;
use Filament\Resources\RelationManagers\RelationManager;

class BlocksRelationManager extends RelationManager
{
    use HasFieldsMapper;

    protected static string $relationship = 'blocks';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Blocks');
    }

    public static function getModelLabel(): string
    {
        return __('Block');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Blocks');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->allowDuplicates()
            ->recordTitleAttribute('name')
            ->reorderable('position')
            ->defaultSort('position', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->limit(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (!$state) {
                                    return;
                                }

                                $block = Block::with('fields')->find($state);

                                if (!$block) {
                                    return;
                                }

                                $customFields = $this->resolveCustomFields();

                                foreach ($block->fields as $field) {
                                    dd($this->resolveFieldInput($field, $customFields));
                                }

                                dd($block->fields);
                                // // Here you might want to do something with the fields
                                // // For example, you could set them in the form state
                                // foreach ($block->fields as $field) {
                                //     $set("field_{$field->id}", $field->default_value ?? null);
                                // }
                            })
                    ])
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
