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

Route::get('schedule/print', 'API\ScheduleController@print');
Route::get('schedule/holiday', 'API\ScheduleController@holiday');

Route::get('schedule/excel', 'API\ScheduleController@export');
Route::post('schedule/excel', 'API\ScheduleController@import');

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::middleware('auth:api')->group(function () {
    Route::post("register", "API\AuthController@register");
    Route::get("user", "API\AuthController@user");
    Route::get("logout", "API\AuthController@logout");
    Route::get('shift/departemen/{id}', 'API\ShiftController@getDepartemens');
    Route::put('shift/departemen/{id}', 'API\ShiftController@updateDepartemens');
    Route::get('job/departemen/{id}', 'API\JobController@getDepartemens');
    Route::put('job/departemen/{id}', 'API\JobController@updateDepartemens');

    Route::apiResource('absen', 'API\AbsenController');
    Route::apiResource('akses', 'API\AksesController');
    Route::apiResource('bagian', 'API\BagianController');
    Route::apiResource('departemen', 'API\DepartemenController');
    Route::apiResource('karyawan', 'API\KaryawanController');
    Route::apiResource('pegawai', 'API\PegawaiController');
    Route::apiResource('ruang', 'API\RuangController');
    Route::apiResource('schedule/change', 'API\ScheduleChangeController');
    Route::apiResource('schedule/request', 'API\ScheduleRequestController');
    Route::apiResource('schedule/assessor', 'API\ScheduleAssessorController');
    Route::apiResource('schedule/access', 'API\ScheduleAccessController');
    Route::apiResource('schedule/order', 'API\ScheduleOrderController');
    Route::apiResource('schedule', 'API\ScheduleController');
    Route::apiResource('shift', 'API\ShiftController');
    Route::apiResource('job', 'API\JobController');
    Route::apiResource('pendapatan/harian', 'API\PendapatanHarianController');
});
