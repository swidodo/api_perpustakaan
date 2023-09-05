<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    echo "<center> Welcome </center>";
});

$router->get('/version', function () use ($router) {
    return $router->app->version();
});

Route::group([

    'prefix' => 'api'

], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('user-profile', 'AuthController@me');

    // manage buku
    Route::get('get-buku', 'BukuController@index');
    Route::post('create-buku', 'BukuController@store');
    Route::get('edit-buku/{id}', 'BukuController@edit');
    Route::put('update-buku/{id}', 'BukuController@update');
    Route::delete('delete-buku/{id}', 'BukuController@destroy');

    // manage anggota
    Route::get('get-anggota', 'AnggotaController@index');
    Route::post('create-anggota', 'AnggotaController@store');
    Route::get('edit-anggota/{id}', 'AnggotaController@edit');
    Route::put('update-anggota/{id}', 'AnggotaController@update');
    Route::delete('delete-anggota/{id}', 'AnggotaController@destroy');

    // transaction pinjam
    Route::get('get-pinjam', 'TranspinjamController@index');
    Route::post('create-pinjam', 'TranspinjamController@store');
    Route::get('edit-pinjam/{id}', 'TranspinjamController@edit');
    Route::put('update-pinjam/{id}', 'TranspinjamController@update');

    // stock
    Route::get('get-stock', 'StockController@index');
    Route::get('get-history', 'TranspinjamController@history');

});

