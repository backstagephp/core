<?php

namespace Backstage\Resources\TemplateResource\RelationManagers;

use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Models\Block;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('fields', []);

                                if (! $state) {
                                    return;
                                }

                                $block = Block::with('fields')->find($state);

                                if (! $block) {
                                    return;
                                }

                                $customFields = $this->resolveCustomFields();

                                $schema = [];

                                foreach ($block->fields as $field) {
                                    $resolvedField = $this->resolveFieldInput($field, $customFields);
                                    if ($resolvedField) {
                                        $schema[] = [
                                            'type' => get_class($resolvedField),
                                            'name' => $field->name,
                                            'label' => $resolvedField->getLabel(),
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
                                        ->label($definition['label'])
                                        ->statePath('values.' . $definition['name']);
                                })->toArray();
                            })
                            ->statePath('fields')
                            ->reactive(),
                    ])->using(function (array $data): array {
                        dd($data);

                        return [
                            'fields' => json_encode($data['fields'] ?? []),
                            // Include any other fields you want to save in the pivot table
                        ];
                    }),
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
