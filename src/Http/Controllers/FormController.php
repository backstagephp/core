<?php

namespace Backstage\Http\Controllers;

use Backstage\Events\FormSubmitted;
use Backstage\Models\Content;
use Backstage\Models\Form;
use Illuminate\Http\Request;

class FormController
{
    public function submit(Request $request, Form $form)
    {
        $request->validate(
            $form->fields->mapWithKeys(function ($field) {
                if ($field->config['required'] ?? false) {
                    $field->rules = ['required'];
                }

                return [$field->slug => $field->rules];
            })
                ->filter()
                ->merge(['content_ulid' => ['nullable', 'exists:content,ulid']])
                ->toArray()
        );

        $content = Content::where('ulid', $request->input('content_ulid'))->first();

        $submission = $form->submissions()->create([
            'site_ulid' => $content?->site_ulid ?? null,
            'language_code' => $content?->language_code ?? null,
            'content_ulid' => $content?->ulid ?? null,
            'submitted_by' => $request->user()?->ulid ?? null,
            'ip_address' => $request->ip(),
            'hostname' => $request->server('REMOTE_HOST'),
            'user_agent' => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        $submission->values()->createMany(
            $form->fields->map(function ($field) use ($request) {
                return [
                    'field_ulid' => $field->ulid,
                    'value' => $request->input($field->slug),
                ];
            })->toArray()
        );

        FormSubmitted::dispatch($submission);

        return redirect()->back()->with('success', __('Your submission has been received.'));
    }
}
