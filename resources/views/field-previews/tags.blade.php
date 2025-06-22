@if (!empty($value) && is_array($value))
    <div class="text-sm text-gray-700">
        @foreach ($value as $tag)
            <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                #{{ $tag }}
            </span>
        @endforeach
    </div>
@elseif (!empty($value) && !is_array($value))
    <div class="text-sm text-gray-700">
        <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
            #{{ $value }}
        </span>
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No tags added
    </div>
@endif 