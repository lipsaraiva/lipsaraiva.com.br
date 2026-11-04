<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/artigos', 'articles')->name('artigos.index');
    Route::get('/artigos/{post}', 'article')->name('artigos.show');
    Route::get('/sitemap.xml', 'sitemap');
    Route::post('/contato', 'contact')->middleware('throttle:5,10')->name('contato');
});
