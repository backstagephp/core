@if (!empty($value))
    @if (is_array($value) && isset($value['url']))
        <div class="text-sm text-gray-700">
            <a href="{{ $value['url'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                {{ $value['text'] ?? $value['url'] }}
            </a>
        </div>
    @elseif (filter_var($value, FILTER_VALIDATE_URL))
        <div class="text-sm text-gray-700">
            <a href="{{ $value }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                {{ $value }}
            </a>
        </div>
    @else
        <div class="text-sm text-gray-700">
            {{ $value }}
        </div>
    @endif
@else
    <div class="text-sm text-gray-400 italic">
        No link entered
    </div>
@endif 