<div {{ $attributes }}>
    @foreach($blocks as $block)
        @php($className = \Vormkracht10\Backstage\Facades\Backstage::resolveComponent($block['type']))
        {!! Blade::renderComponent(
            new $className(['_type' => $block['type']] + $block['data'])
        ) !!}
    @endforeach
</div>
