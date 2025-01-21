<?php

namespace Vormkracht10\Backstage\Resources\TemplateResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Fields\Concerns\CanMapDynamicFields;
use Filament\Forms\Components\Grid;

class BlocksRelationManager extends RelationManager
{
    use CanMapDynamicFields;

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
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Initialize with empty schema
                                $set('fields', []);

                                if (! $state) {
                                    return;
                                }

                                $block = Block::with('fields')->find($state);

                                if (! $block) {
                                    return;
                                }

                                $customFields = $this->resolveCustomFields();

                                // Create a serializable array of field definitions
                                $schema = [];
                                foreach ($block->fields as $field) {
                                    $resolvedField = $this->resolveFieldInput($field, $customFields);
                                    if ($resolvedField) {
                                        // Convert the field component to a serializable array
                                        $schema[] = [
                                            'type' => get_class($resolvedField),
                                            'name' => $resolvedField->getName(),
                                            'label' => $resolvedField->getLabel(),
                                            // Add other necessary properties you want to preserve
                                        ];
                                    }
                                }

                                $set('fields', $schema);
                            }),
                        Grid::make()
                            ->columns(1)
                            ->schema(function ($get) {
                                $fieldDefinitions = $get('fields') ?? [];

                                // Reconstruct form components from the serialized data
                                return collect($fieldDefinitions)->map(function ($definition) {
                                    $componentClass = $definition['type'];
                                    return $componentClass::make($definition['name'])
                                        ->label($definition['label']);
                                })->toArray();
                            })
                            ->statePath('fields')
                            ->reactive(),
                    ]),
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
