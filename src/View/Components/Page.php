<?php

namespace Backstage\View\Components;

use Illuminate\View\Component;

class Page extends Component
{
    public function __construct() {}

    public function render()
    {
        return view('backstage::components.page');
    }
}
