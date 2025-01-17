<x-page>
    {{ $content->field('body') }}

    @dump($content->body)

    @dd ($content->field('authors'))

    <x-blocks field="blocks" />
    <x-blocks field="main" />
</x-page>