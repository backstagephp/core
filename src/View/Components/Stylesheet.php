<?php

namespace Vormkracht10\Backstage\View\Components;

use Illuminate\View\Component;

class Stylesheet extends Component
{
    public function __construct(public ?string $title = null)
    {
        $this->title = $title;
    }

    public function render()
    {
        return view('backstage::components.page');
    }
}
