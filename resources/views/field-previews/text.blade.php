@if (!empty($value))
    <div class="text-sm text-gray-700 bg-red-500">
        {{ $value }}
    </div>
@else
    <div class="text-sm text-gray-400 italic bg-blue-500">
        No text entered
    </div>
@endif 