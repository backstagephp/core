<?php

namespace Vormkracht10\Backstage\View\Components;

use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Vormkracht10\Backstage\Models\Form as ModelsForm;

class Form extends Component
{
    public ?ModelsForm $form;

    public function __construct(public ?string $slug = null)
    {
        $this->slug = $slug;
        $this->form = ModelsForm::where('slug', $this->slug)
            ->with('fields')
            ->first();
    }

    public function shouldRender()
    {
        return $this->form !== null;
    }

    public function render()
    {
        return View::first([
            'components.forms.' . $this->slug,
            'components.forms.default',
            'backstage::components.form',
        ]);
    }
}
