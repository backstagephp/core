<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Form;

Route::fallback(function (Request $request) {
    abort_unless($request->content(), 404);

    if (! $request->isMethod('GET')) {
        return abort(405, 'Method Not Allowed');
    }

    return $request->content()->response();
});

Route::post('/forms/{form}', function (Request $request, Form $form) {
    $request->validate(
        $form->fields->mapWithKeys(function ($field) {
            if ($field->config['required'] ?? false) {
                $field->rules[] = 'required';
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
        'country_code' => $content?->country_code ?? null,
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

    return redirect()->back()->with('success', 'Your submission has been received.');
})->middleware('web')->name('backstage.forms.submit');