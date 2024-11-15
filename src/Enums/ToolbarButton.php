<?php

namespace Vormkracht10\Backstage\Enums;

use Vormkracht10\Backstage\Concerns\EnumArraySerializableTrait;

enum ToolbarButton: string
{
    use EnumArraySerializableTrait;

    case AttachFiles = 'attachFiles';
    case Blockquote = 'blockquote';
    case Bold = 'bold';
    case BulletList = 'bulletList';
    case CodeBlock = 'codeBlock';
    case H2 = 'h2';
    case H3 = 'h3';
    case Italic = 'italic';
    case Link = 'link';
    case OrderedList = 'orderedList';
    case Redo = 'redo';
    case Strike = 'strike';
    case Underline = 'underline';
    case Undo = 'undo';
}
