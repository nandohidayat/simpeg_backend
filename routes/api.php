<?php

use Illuminate\Http\Request;

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

Route::post("login", "API\AuthController@login");
Route::post("register", "API\AuthController@register");

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('karyawan', 'API\KaryawanController');

Route::middleware('auth:api')->group(function () {
    Route::get("logout", "API\AuthController@logout");
    Route::post("user", "API\AuthController@user");

    Route::get('penilaian/{id}/update', 'API\PenilaianController@updateDetail');

    Route::group(['middleware' => 'cors'], function () {
        Route::resource('pegawai', 'API\PegawaiController');
        Route::resource('penilaian', 'API\PenilaianController');
    });
});
