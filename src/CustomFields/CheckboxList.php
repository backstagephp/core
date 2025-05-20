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
        if (!isset($field->config['optionType']) || $field->config['optionType'] !== 'relationship' || empty($field->config['relations'])) {
            return $data;
        }

        $relations = DB::table('content_relation')
            ->where('source_type', $record->getMorphClass())
            ->where('source_ulid', $record->ulid)
            ->get();

        if ($relations->isEmpty()) {
            return $data;
        }

        $values = [];
        foreach ($field->config['relations'] as $relation) {
            $resource = $relation['resource'];
            $key = $relation['relationKey'];

            $instance = new static;
            $model = $instance->resolveResourceModel($resource);

            $relatedRecords = $model->whereIn('ulid', $relations->where('target_type', $resource)->pluck('target_ulid'))->get();
            $values = array_merge($values, $relatedRecords->pluck($key)->toArray());
        }

        if (!empty($values)) {
            if (!$field->config['multiple'] ?? false) {
                $values = $values[0];
            }
            $data['values'][$field->ulid] = $values;
        }

        return $data;
    }

    public static function mutateBeforeSaveCallback(Model $record, Field $field, array $data): array
    {
        if (!isset($field->config['optionType']) || $field->config['optionType'] !== 'relationship' || empty($field->config['relations'])) {
            return $data;
        }

        DB::table('content_relation')
            ->where('source_type', $record->getMorphClass())
            ->where('source_ulid', $record->ulid)
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
                DB::table('content_relation')->insert([
                    'source_type' => $record->getMorphClass(),
                    'source_ulid' => $record->ulid,
                    'target_type' => $resource,
                    'target_ulid' => $result->ulid,
                ]);
            }
        }

        return $data;
    }
}