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

Route::group(['prefix' => 'v1'], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::group(['middleware' => 'jwt.verify'], function () {
        Route::post('logout', 'Api\AuthController@logout');
        Route::post('me', 'Api\AuthController@me');
        Route::resource('kategori', 'Api\KategoriController')->except(['create', 'edit']);
        Route::resource('barang', 'Api\BarangController')->except(['create', 'edit']);
    });
});
