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

//        kenapa ditaro disiini, karena mungkin di frontend nanti, si gudang membutuhlkan list of data barang dan data kategori
        Route::get('barang', 'Api\BarangController@index')->name('barang.index');
        Route::get('kategori', 'Api\KategoriController@index')->name('kategori.index');

        Route::group(['middleware' => 'has_role:1'], function () {
            Route::post('laporan', 'Api\LaporanController@report')->name('reporting');
            Route::resource('kategori', 'Api\KategoriController')->except(['create', 'edit', 'index']);
            Route::resource('barang', 'Api\BarangController')->except(['create', 'edit', 'index']);
        });
        Route::group(['middleware' => 'has_role:2'], function () {
            Route::resource('barang_masuk', 'Api\BarangMasukController')->except(['create', 'edit']);
            Route::resource('barang_keluar', 'Api\BarangKeluarController')->except(['create', 'edit']);
        });
    });
});
