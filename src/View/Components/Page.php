<?php

namespace Backstage\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class Page extends Component
{
    public function __construct() {}

    public function render()
    {
        return View::first(['components.page', 'backstage::components.page']);
    }
}
