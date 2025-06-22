@if (!empty($value))
    @if (is_array($value))
        <div class="text-sm text-gray-700">
            @foreach ($value as $option)
                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                    {{ $option }}
                </span>
            @endforeach
        </div>
    @else
        <div class="text-sm text-gray-700">
            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                {{ $value }}
            </span>
        </div>
    @endif
@else
    <div class="text-sm text-gray-400 italic">
        No option selected
    </div>
@endif 