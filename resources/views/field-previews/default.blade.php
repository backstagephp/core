@if (!empty($value))
    <div class="text-sm text-gray-700">
        @if (is_array($value))
            <pre class="text-xs bg-gray-100 p-2 rounded">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
        @else
            {{ $value }}
        @endif
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No value set
    </div>
@endif 