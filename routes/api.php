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

Route::post("login", "API\AuthController@login");
Route::get("user/{id}", "API\AuthController@user");
Route::get('schedule/export/{id}', 'API\ScheduleController@export');


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::middleware('auth:api')->group(function () {
    Route::post("register", "API\AuthController@register");
    Route::get("logout", "API\AuthController@logout");
    Route::get('shift/departemen/{id}', 'API\ShiftController@getDepartemens');
    Route::post('shift/departemen', 'API\ShiftController@createDepartemens');

    Route::apiResource('absen', 'API\AbsenController');
    Route::apiResource('akses', 'API\AksesController');
    Route::apiResource('bagian', 'API\BagianController');
    Route::apiResource('departemen', 'API\DepartemenController');
    Route::apiResource('karyawan', 'API\KaryawanController');
    Route::apiResource('ruang', 'API\RuangController');
    Route::apiResource('schedule/change', 'API\ScheduleChangeController');
    Route::apiResource('schedule', 'API\ScheduleController');
    Route::apiResource('shift', 'API\ShiftController');
    Route::apiResource('pendapatanharian', 'API\PendapatanHarianController');


    // OLD CODE
    Route::get('penilaian/{id}/update', 'API\PenilaianController@updateDetail');
    Route::resource('pegawai', 'API\PegawaiController');
    Route::resource('penilaian', 'API\PenilaianController');
});
