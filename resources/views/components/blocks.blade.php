<div {{ $attributes }}>
    @foreach($blocks as $block)
        <x-dynamic-component :component="$block['type']" :data="$block['data']" />
    @endforeach
</div>
