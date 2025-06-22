@if (!empty($value))
    <div class="text-sm text-gray-700 prose prose-sm max-w-none">
        {!! $value !!}
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No content entered
    </div>
@endif 