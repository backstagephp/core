@if (!empty($value))
    <div class="text-sm text-gray-700">
        <span class="inline-flex items-center">
            <svg class="w-3 h-3 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="8" fill="currentColor"/>
            </svg>
            {{ $value }}
        </span>
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No option selected
    </div>
@endif 