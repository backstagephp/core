---
title: Forms
---

## Overview

Forms, form fields and actions can be managed through Backstage.

## Setup

1. Create a form in Forms;
2. (optional) create a blade file located in:
- resources/views/components/forms/{slug}.blade.php
- resources/views/components/forms/default.blade.php

The following variables are available.

```php
@dump($slug, $form, $content)
```

The default form blade looks like this:

```html
<div {{ $attributes }}>
    <form method="POST" action="{{ route('backstage.forms.submit', $form->slug) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="content_ulid" value="{{ $content->ulid }}">
        @foreach ($form->fields as $field)
            <div>

                <label for="{{ $field->slug }}">{{ $field->name }}</label>
                <input type="{{ $field->type }}" name="{{ $field->slug }}" id="{{ $field->slug }}">

                @error($field->slug)
                    <p>{{ $message }}</p>
                @enderror

            </div>
        @endforeach

        <button type="submit">{{ $form->submit_button ?? __('Submit') }}</button>
    </form>
</div>
```

### Form component

Use the following blade component to include the form in your blade files:

```blade
<x-form slug="contact" />
```


### Form Actions

You can add one or more actions for each form. Actions are executed after succesful submission.