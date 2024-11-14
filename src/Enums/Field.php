<?php

namespace Vormkracht10\Backstage\Enums;

use Vormkracht10\Backstage\Concerns\EnumArraySerializableTrait;

enum Field: string
{
    use EnumArraySerializableTrait;

    case Builder = 'builder';
    case Checkbox = 'checkbox';
    case Color = 'color';
    case DateTime = 'datetime';
    case File = 'file-upload';
    case Hidden = 'hidden';
    case KeyValue = 'key-value';
    case MarkdownEditor = 'markdown-editor';
    case Radio = 'radio';
    case Repeater = 'repeater';
    case RichEditor = 'rich-editor';
    case Select = 'select';
    case Tags = 'tags';
    case Text = 'text';
    case Textarea = 'textarea';
    case Toggle = 'toggle';
    case ToggleButtons = 'toggle-buttons';
        // custom
    case Media = 'media';
    case Link = 'link';
}