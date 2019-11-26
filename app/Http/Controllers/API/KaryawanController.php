<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = DB::table('karyawans');

        error_log("START THE APPOCALIPSE");

        if (request()->select == 1) {
            $ruang = Karyawan::where('nik', Auth::user()->nik)->first()->id_ruang;
            $query->where('id_ruang', $ruang);
            $query->select('nik', 'nama');
            $query->orderBy('nama', 'asc');
        } else {
            $query->join('ruangs', 'karyawans.id_ruang', '=', 'ruangs.id_ruang');
            $query->join('departemens', 'karyawans.id_departemen', '=', 'departemens.id_departemen');
            $query->select('karyawans.nik', 'karyawans.nama', 'ruangs.ruang', 'departemens.departemen');
            $query->orderBy('nik', 'desc');
        }
        $data = $query->get();

        return response()->json(["status" => "success", "data" => $data], 200);
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

        return response()->json(["status" => "success"], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Karyawan::where('nik', $id)
            ->join('departemens', 'karyawans.id_departemen', '=', 'departemens.id_departemen')
            ->join('ruangs', 'karyawans.id_ruang', '=', 'ruangs.id_ruang')
            ->select('karyawans.nik', 'karyawans.nama', 'departemens.departemen', 'ruangs.ruang')
            ->first();

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Karyawan::updateOrCreate(['nik' => $id], $request->all());

        return response()->json(["status" => "success"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Karyawan::find($id);

        if ($data === null) return response()->json(["status" => "not found"], 404);

        $data->delete();

        return response()->json(["status" => "success"], 201);
    }
}
