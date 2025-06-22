@if (!empty($value) && is_array($value))
    <div class="text-sm text-gray-700">
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ count($value) }} item{{ count($value) !== 1 ? 's' : '' }}</span>
        </div>
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No items added
    </div>
@endif 