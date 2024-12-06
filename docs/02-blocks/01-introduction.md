---
title: Getting started 
---

## Overview

Blocks are collections of fields that are rendered in blade components.

## Setup

Create a component.

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
use Vormkracht10\Backstage\Facades\Backstage;

Backstage::registerComponent(CallToAction::class);
```

Then this component should be available in Backstage. You should add the required fields to the block.