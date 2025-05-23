<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\SocialAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])->where('provider', 'google|microsoft');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])->where('provider', 'google|microsoft');
