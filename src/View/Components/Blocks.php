<?php

namespace Vormkracht10\Backstage\View\Components;

use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class Blocks extends Component
{
    public function __construct(
        public string $field = '',
    ) {
        $this->content = View::shared('content');
    }

    public function render()
    {
        $blocks = $this->content->blocks($this->field);

        dump($blocks);

        return view('backstage::components.blocks', compact('blocks'));
    }
}
