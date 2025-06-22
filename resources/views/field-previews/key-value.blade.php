@if (!empty($value) && is_array($value))
    <div class="text-sm text-gray-700">
        @foreach ($value as $key => $val)
            <div class="mb-1">
                <span class="font-medium">{{ $key }}:</span>
                <span class="ml-1">{{ $val }}</span>
            </div>
        @endforeach
    </div>
@elseif (!empty($value))
    <div class="text-sm text-gray-700">
        {{ $value }}
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No key-value pairs added
    </div>
@endif 