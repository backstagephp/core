<div {{ $attributes }}>
    @foreach($blocks as $block)
        {!! Blade::renderComponent(new Vormkracht10\Backstage\View\Components\Blocks\Heading(...$block['data'])) !!}
    @endforeach
</div>
