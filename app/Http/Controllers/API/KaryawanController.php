<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Karyawan;
use App\Penilaian;
use App\Rekan;
use App\Ruang;
use Illuminate\Support\Facades\DB;
use stdClass;
use Validator;


class KaryawanController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Karyawan::with('departemen', 'ruang')->get();
        // $data = DB::table('karyawans')
        //     ->join('ruangs', 'karyawans.ruang_id', '=', 'ruangs.id')
        //     ->join('departemens', 'karyawans.departemen_id', '=', 'departemens.id')
        //     ->select('karyawans.nik', 'karyawans.nama', 'ruangs.ruang', 'departemens.departemen')
        //     ->get();

        return $this->sendResponse($data, 'Product retrieved successfully.');
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

        return $this->sendResponse([], 'Product retrieved successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    { }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Penilaian $penilaian)
    { }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Penilaian $penilaian)
    { }
}
