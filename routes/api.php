<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['cors', 'json.response', 'auth:api'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['cors', 'json.response']], function () {
    // public routes
    Route::post('/login', 'Auth\ApiAuthController@login')->name('login.api');
    Route::post('/register','Auth\ApiAuthController@register')->name('register.api');
    Route::post('/logout', 'Auth\ApiAuthController@logout')->name('logout.api');

    // GET Requests
    Route::get('/currencies','CurrencyController@index')->name('currency.index');
});



Route::group(['middleware' => ['cors', 'json.response', 'auth:api']], function () {
    Route::get('/articles', 'ArticleController@index')->middleware('api.admin')->name('articles');

    // POST Requests
    Route::post('/currencies', 'CurrencyController@store')->name('currency.index');

    // PUT Requests
    Route::put('/currencies/{id}', 'CurrencyController@update')->name('currency.update');

    // DELETE Requests
    Route::delete('/currencies/{id}', 'CurrencyController@destroy')->middleware('api.admin')->name('currency.delete');

});

