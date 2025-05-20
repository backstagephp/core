<?php 

namespace Backstage\CustomFields;

use Backstage\Fields\Models\Field;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Backstage\Fields\Fields\CheckboxList as Base;

class CheckboxList extends Base
{
    public static function mutateFormDataCallback(Model $record, Field $field, array $data): array
    {
        return $data;
    }

    public static function mutateBeforeSaveCallback(Model $record, Field $field, array $data): array
    {
        if (!isset($field->config['optionType']) || $field->config['optionType'] !== 'relationship' || empty($field->config['relations'])) {
            return $data;
        }

        DB::table('relationables')
            ->where('relation_type', $record->getMorphClass())
            ->where('relation_ulid', $record->ulid)
            ->delete();
        
        $values = $data['values'][$field->ulid];
        if (!is_array($values)) {
            $values = [$values];
        }
        
        foreach ($field->config['relations'] as $relation) {
            $resource = $relation['resource'];
            $key = $relation['relationKey'];

            $instance = new static;
            $model = $instance->resolveResourceModel($resource);

            $results = $model->whereIn($key, $values)->get();

            foreach ($results as $result) {
                DB::table('relationables')->insert([
                    'relation_type' => $record->getMorphClass(),
                    'relation_ulid' => $record->ulid,
                    'related_type' => $resource,
                    'related_ulid' => $result->ulid,
                ]);
            }
        }

        return $data;
    }
}