<?php

namespace Vormkracht10\Backstage\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BackstageExample extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $name = 'John Doe',
        public ?string $function = 'Developer',
        public ?array $skills = [],
        public ?bool $active = true
    ) {
    }
    
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('backstage::components.backstage-example');
    }
}
