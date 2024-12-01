<?php

namespace Vormkracht10\Backstage\View\Components;

use Illuminate\View\Component;

class Form extends Component
{
    public function __construct(public ?string $slug = null)
    {
        $this->slug = $slug;
    }

    public function render()
    {
        return view('backstage::components.form');
    }
}
