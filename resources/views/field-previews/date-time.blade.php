@if (!empty($value))
    <div class="text-sm text-gray-700">
        @if (is_string($value))
            {{ \Carbon\Carbon::parse($value)->format('M j, Y g:i A') }}
        @else
            {{ $value->format('M j, Y g:i A') }}
        @endif
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No date/time selected
    </div>
@endif 