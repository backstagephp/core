@if ($blocks)
    <div {{ $attributes }}>
        @foreach($blocks as $block)
            @php($className = \Backstage\Facades\Backstage::resolveComponent($block['type']))
            @php($params = \Backstage\Facades\Backstage::mapParams($block))
            @php($component = $className::resolve($params))
            @if ($component->shouldRender())
            {!! \Illuminate\Support\Facades\Blade::renderComponent($component) !!}
            @endif
        @endforeach
    </div>
@endif