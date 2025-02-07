<?php

use Backstage\Http\Controllers\FormController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::fallback(function (Request $request) {

    abort_unless($request->content(), 404);

    if (! $request->isMethod('GET')) {
        return abort(405, 'Method Not Allowed');
    }

    return $request->content()->response();
});

Route::post('/forms/{form}', [FormController::class, 'submit'])->middleware('web')->name('backstage.forms.submit');
