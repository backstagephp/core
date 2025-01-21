---
title: Content
---

## Overview

Content is the basic for each page.

### Blade file
The blade file that is being rendered is based on the following conditions:


1. The `template_slug` value for the requested content. For example: 'content.sidebar-left'.
2. The `/resources/views/types/{content_type}.blade.php` where `content_type` is the slug of the type.
3. The `/resources/views/types/default.blade.php`. This file can be used for all content that doesnt have a specific blade file.
4. A default blade file inside Backstage.

### Variables inside blade

The `$content` variable is always available. This is the current requested content. This is by default a `Vormkracht10\Backstage\Models\Content` model.


### Blade file example

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