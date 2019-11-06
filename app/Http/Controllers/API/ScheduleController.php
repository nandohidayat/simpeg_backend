<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Karyawan;
use App\Penilaian;
use App\Rekan;
use App\Ruang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;
use Validator;

use function GuzzleHttp\Promise\all;

class ScheduleController extends BaseController
{
    private $objFoo;

    public function __construct(Karyawan $karyawan)
    {
        $this->objFoo = $karyawan;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tahun, $bulan)
    {
        $this->objFoo->today = Carbon::now();
        $data = Karyawan::with('schedules')->orderBy('nik', 'asc')->get();

        return $this->sendResponse($data, "Sukses mang, yeyeyeyeye");
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        Karyawan::create($input);

        return $this->sendResponse([], 'Sukses mang, yeyeyeyeye');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Karyawan::where('nik', $id)->with('departemen', 'ruang')->first();
        return $this->sendResponse($data, 'Sukses mang, yeyeyeyeye');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        Karyawan::updateOrCreate(['nik' => $request->nik], $request->all());
        return $this->sendResponse([], 'Sukses mang, yeyeyeyeye');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();
        return $this->sendResponse([], 'Sukses mang, yeyeyeyeye');
    }

    public function getSchedules($tahun, $bulan)
    { }
}