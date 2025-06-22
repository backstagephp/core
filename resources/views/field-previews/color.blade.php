@if (!empty($value))
    <div class="text-sm text-gray-700">
        <div class="flex items-center">
            <div class="w-4 h-4 rounded border border-gray-300 mr-2" style="background-color: {{ $value }};"></div>
            <span class="font-mono text-xs">{{ $value }}</span>
        </div>
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No color selected
    </div>
@endif 