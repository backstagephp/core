---
title: Content
---

# Content

Content is the foundation of each page. Every content item has a [type](/02-types/01-introduction) defined by configurable [fields](/03-fields/01-introduction).

## Blade

Blade templates are resolved in this order:

1. The `template_slug` value for the content (e.g., `content.sidebar-left`).
2. `/resources/views/types/{content_type}.blade.php` (where `{content_type}` is the type slug).
3. `/resources/views/types/default.blade.php` (fallback for types without a custom template).
4. The Backstage default blade file.

### Variables

- `$content`: Always available, represents the current `Backstage\Models\Content` instance.

### Example

```php
<x-page>
    {{ $content->field('body') }}

    @foreach ($content->field('authors') as $author)
        {{ $author->field('name') }}<br />
    @endforeach

    <x-blocks field="blocks" />
    <x-blocks field="main" />
</x-page>
```
