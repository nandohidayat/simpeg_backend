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
Route::get('try', "API\AuthController@try");

Route::get('absen/export', 'API\AbsenController@export');

Route::get('schedule/print', 'API\ScheduleController@print');
Route::get('schedule/holiday', 'API\ScheduleController@holiday');

Route::get('schedule/excel', 'API\ScheduleController@export');
Route::post('schedule/excel', 'API\ScheduleController@import');

Route::get('pendapatanpeg/profilp', 'API\PendapatanPegController@exportTemplatePeg');
Route::get('pendapatanpeg/pendapatan', 'API\PendapatanPegController@exportPendapatanPeg');
Route::post('pendapatanpeg/pendapatan', 'API\PendapatanPegController@importPendapatanPeg');
Route::get('pendapatanpeg/profil', 'API\PendapatanPegController@getProfil');

Route::get('email/test', 'API\PendapatanEmailController@test');
Route::get('email/kirim', 'API\PendapatanPegController@kirimEmail');
Route::post('email/buat', 'API\PendapatanPegController@buatEmail');


Route::middleware('auth:api')->group(function () {
    Route::post("register", "API\AuthController@register");
    Route::post("refresh", "API\AuthController@refresh");
    Route::post("logout", "API\AuthController@logout");
    Route::get("user", "API\AuthController@user");
    Route::put("password/{id}", "API\AuthController@password");
    Route::put('reset/{id}', 'API\AuthController@reset');
    Route::delete('delete/{id}', 'API\AuthController@delete');

    Route::get('shift/departemen/{id}', 'API\ShiftController@getDepartemens');
    Route::put('shift/departemen/{id}', 'API\ShiftController@updateDepartemens');
    Route::get('job/departemen/{id}', 'API\JobController@getDepartemens');
    Route::put('job/departemen/{id}', 'API\JobController@updateDepartemens');

    Route::get('pendapatanpeg', 'API\PendapatanPegController@getPendapatan');

    Route::apiResource('absen', 'API\AbsenController');
    Route::apiResource('akses', 'API\AksesController');
    Route::apiResource('bagian', 'API\BagianController');
    Route::apiResource('departemen', 'API\DepartemenController');
    Route::apiResource('group', 'API\GroupController');
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
    Route::apiResource('pendapatan/email', 'API\PendapatanEmailController');
    Route::apiResource('pendapatan/harian', 'API\PendapatanHarianController');
    Route::apiResource('pendapatan/list', 'API\PendapatanListController');
    Route::apiResource('pendapatan/makan', 'API\PendapatanMakanController');
    Route::apiResource('pendapatan/profil', 'API\PdptProfilController');
    Route::apiResource('pendapatan', 'API\PendapatanController');
    Route::apiResource('log/departemen', 'API\LogDepartemenController');
});
