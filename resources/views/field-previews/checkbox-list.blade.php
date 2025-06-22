@if (!empty($value) && is_array($value))
    <div class="text-sm text-gray-700">
        @foreach ($value as $option)
            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                {{ $option }}
            </span>
        @endforeach
    </div>
@elseif (!empty($value) && !is_array($value))
    <div class="text-sm text-gray-700">
        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
            {{ $value }}
        </span>
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No options selected
    </div>
@endif 