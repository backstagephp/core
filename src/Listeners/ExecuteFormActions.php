<?php

namespace Vormkracht10\Backstage\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Vormkracht10\Backstage\Events\FormSubmitted;
use Vormkracht10\Backstage\Models\FormSubmission;

class ExecuteFormActions implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FormSubmitted $event): void
    {
        //
        if (!$event->formSubmission?->form?->formActions) {
            return;
        }

        foreach ($event->formSubmission->form->formActions as $action) {
            $action->execute($event->formSubmission);
        }
    }
}
