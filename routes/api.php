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
Route::get("user/{id}", "API\AuthController@user");

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::apiResource('departemen', 'API\DepartemenController');
Route::apiResource('bagian', 'API\BagianController');
Route::apiResource('ruang', 'API\RuangController');
Route::apiResource('akses', 'API\AksesController');

Route::resource('karyawan', 'API\KaryawanController');
Route::resource('shift', 'API\ShiftController');
Route::get('schedule/{tahun}/{bulan}', 'API\ScheduleController@index');
Route::post('schedule/{tahun}/{bulan}', 'API\ScheduleController@store');

Route::middleware('auth:api')->group(function () {
    Route::post("register", "API\AuthController@register");
    Route::get("logout", "API\AuthController@logout");
    Route::post("user", "API\AuthController@user");

    Route::get('penilaian/{id}/update', 'API\PenilaianController@updateDetail');

    Route::group(['middleware' => 'cors'], function () {
        Route::resource('pegawai', 'API\PegawaiController');
        Route::resource('penilaian', 'API\PenilaianController');
    });
});
