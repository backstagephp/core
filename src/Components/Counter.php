<?php

namespace Backstage\Components\Counter\Components;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class Counter extends Component
{
    public $count;

    public function __construct()
    {
        $this->count = Cache::get('counter', 0);
        $this->count++;

        Cache::put('counter', $this->count);
    }

    public function render()
    {
        return view('components.counter.counter');
    }
}
