<?php

namespace Vormkracht10\Backstage\View\Components;

use Illuminate\View\Component;

class Paragraph extends Component
{
    public function __construct(public ?string $text = null)
    {
        $this->text = $text;
    }

    public function render()
    {
        return view('backstage::components.blocks.paragraph');
    }
}
