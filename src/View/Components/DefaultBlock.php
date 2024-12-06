<?php

namespace Vormkracht10\Backstage\View\Components;

use Illuminate\Support\Facades\View;
use Illuminate\View\Component;

class DefaultBlock extends Component
{
    public function __construct(public array $data = []) {
        $this->content = View::shared('content');
    }

    public function render()
    {
        return View::first([
            'components.blocks.' . $this->data['_type'],
            'components.blocks.default',
            'backstage::components.default'
        ]);
    }

    public function data()
    {
        return array_merge(parent::data(), $this->data);
    }
}
