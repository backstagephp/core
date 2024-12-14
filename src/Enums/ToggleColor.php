<?php

namespace Vormkracht10\Backstage\Enums;

use Vormkracht10\Backstage\Concerns\EnumArraySerializableTrait;

enum ToggleColor: string
{
    use EnumArraySerializableTrait;

    case DANGER = 'danger';
    case GRAY = 'gray';
    case INFO = 'info';
    case PRIMARY = 'primary';
    case SUCCESS = 'success';
    case WARNING = 'warning';
}
