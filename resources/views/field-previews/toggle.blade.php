@if (!empty($value) && $value !== '0' && $value !== false)
    <div class="text-sm text-gray-700">
        <span class="inline-flex items-center">
            <div class="w-4 h-4 bg-green-500 rounded-full mr-2"></div>
            On
        </span>
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        <span class="inline-flex items-center">
            <div class="w-4 h-4 bg-gray-300 rounded-full mr-2"></div>
            Off
        </span>
    </div>
@endif 