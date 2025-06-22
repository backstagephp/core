@if (!empty($value))
    <div class="text-sm text-gray-700 whitespace-pre-wrap">
        {{ $value }}
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No content entered
    </div>
@endif 