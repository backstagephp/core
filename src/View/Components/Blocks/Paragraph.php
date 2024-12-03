<?php

namespace Vormkracht10\Backstage\View\Components\Blocks;

use Illuminate\View\Component;

class Paragraph extends Component
{
    public function __construct(
        public array $data = []
    ) {}

    public function render()
    {
        return view('backstage::components.blocks.paragraph', $this->data);
    }
}
