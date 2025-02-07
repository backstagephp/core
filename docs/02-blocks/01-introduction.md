---
title: Getting started 
---

## Overview

Blocks are collections of fields that are rendered in blade components.

## Default usage

When creating blocks, components and views are automaticly be loaded if exists.

To gain more control of components see advanced setup.

### Components without fields

You can create blocks that do not contain any fields but still wanna add this as option in the block builder. You can do the following:

1. Create a block in Backstage, for example: Name: "Promotion Banner" and slug: "promotion-banner".

![afbeelding](https://github.com/user-attachments/assets/ad526b50-799d-4f87-9b71-d9aa963db4c8)

3. Create a view in: /resources/views/blocks/promotion-banner.blade.php
4. When adding a new block to the builder you can select this block and the view will be rendered.

![afbeelding](https://github.com/user-attachments/assets/3d3f9c6b-8778-4627-ace2-8c6bed0beca6)

### Components with fields

When creating blocks, a component can also automaticly be loaded. Useful when having fields and (may) need some custom logic.

1. Create a block in Backstage, for example: Name "Call to Action", and slug: "call-to-action".
2. Create a component in /app/View/Components/CallToAction.php
3. Add the fields in the constructor:
```php
<?php
// /app/View/Components/CallToAction.php
namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CallToAction extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public $text = '', public $url = '', public $urlText = '')
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.call-to-action');
    }
}
```
4. Create the file /resources/views/components/call-to-action.blade.php
```html
<div>
    {{ $text }}<br />
    <a href="{{ $url }}">{{$urlText}}</a>
</div>
```

## Advanced setup

Create a component:

```bash
php artisan make:component CallToAction
```

Add field you may use for this component
```php
public function __construct(public string $url, public string $text = 'Click me')
{
}
```

Next, if you wanna have this component available in the blocks register them (e.g. AppServiceProvider):

```php
use Backstage\Facades\Backstage;

Backstage::registerComponent(CallToAction::class);
```

You can also add this component to `config/backstage.php`.

```php
return [
    // ...
    'components' => [
        'blocks' => [
            Backstage\View\Components\Blocks\Heading::class,
            Backstage\View\Components\Blocks\Paragraph::class,
            App\View\Components\CallToAction::class
        ],
    ];
];
```

Then this component should be available in Backstage. You should add the required fields to the block.
