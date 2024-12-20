---
title: Forms
---

## Overview

Forms, form fields and actions can be managed through Backstage.

## Setup

1. Create a form in Forms;
2. (optional) create a blade file located in:
- resources/forms/{slug}.blade.php
- resources/forms/default.blade.php

The following variables are available.

```php
@dump($slug, $form, $content)
```

### Form component

Use the following blade component to include the form in your blade files:

```blade
<x-form slug="contact" />
```