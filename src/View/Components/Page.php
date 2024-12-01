<?php

namespace Vormkracht10\Backstage\View\Components;

use Illuminate\View\Component;
use Vormkracht10\Backstage\Models\Content;

class Page extends Component
{
    public function __construct() {}

    public function render()
    {
        return view('backstage::components.page');
    }
}
