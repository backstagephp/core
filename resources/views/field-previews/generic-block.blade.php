@php
    $allVars = get_defined_vars();
    $block = \Backstage\Models\Block::where('ulid', $allVars['block_ulid'])->with('fields')->first();

    $fields = $block->fields;
    $mappedFields = $fields->map(function ($field) use ($allVars) {
        $fieldData['name'] = $field->name;
        $fieldData['value'] = $allVars['__data'][$field->ulid];
        $fieldData['type'] = $field->field_type;
        $fieldData['ulid'] = $field->ulid;
        $fieldData['slug'] = $field->slug;

        return $fieldData;
    });
@endphp

<div class="p-4 space-y-4">
    @foreach ($mappedFields as $field)
        <div class="border-b border-gray-200 pb-3 last:border-b-0">
            <div class="text-xs font-medium text-gray-500 mb-1">{{ $field['name'] }}</div>
            <div class="max-w-md">
                {!! render_field_preview($field['type'], $field['value'], $field['name']) !!}
            </div>
        </div>
    @endforeach
</div>

