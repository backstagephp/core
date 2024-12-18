@if ($blocks)
    <div {{ $attributes }}>
        @foreach($blocks as $block)
            @php($className = \Vormkracht10\Backstage\Facades\Backstage::resolveComponent($block['type']))
            @php($component = $className::resolve($block['data'] + ['_type' => $block['type']]))
            @if ($component->shouldRender())
            {!! \Illuminate\Support\Facades\Blade::renderComponent($component) !!}
            @endif
        @endforeach
    </div>
@endif