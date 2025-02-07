<?php

namespace Backstage\View\Components;

use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Backstage\Models\Content;

class Blocks extends Component
{
    public function __construct(
        public string $field = '',
        public ?Content $content = null
    ) {
        $this->content = View::shared('content');
    }

    public function render()
    {
        $blocks = $this->content->blocks($this->field);

        return view('backstage::components.blocks', compact('blocks'));
    }
}
