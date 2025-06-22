@if (!empty($value))
    <div class="text-sm text-gray-700 prose prose-sm max-w-none">
        {!! \Illuminate\Support\Str::markdown($value) !!}
    </div>
@else
    <div class="text-sm text-gray-400 italic">
        No markdown content entered
    </div>
@endif 