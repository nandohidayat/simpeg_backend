<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Karyawan;
use App\SIMDataPegawai;
use App\SIMLoginPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;
use Tymon\JWTAuth\Facades\JWTAuth;

class KaryawanController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = DB::table('f_data_pegawai as fdp');
        if ((int)request()->select === 1) {
            $query->whereRaw('\'' . request()->dept . '\' = ANY(fdp.id_dept)');
            $query->select('fdp.id_pegawai', 'fdp.nm_pegawai');
        } else {
            $query->leftJoin('f_department as fd', 'fd.id_dept', '=', DB::raw('ANY(fdp.id_dept)'));
            $query->select('fdp.id_pegawai', 'fdp.nik_pegawai as nik', 'fdp.nm_pegawai as nama', 'fdp.jenis_kelamin as kelamin', 'fd.nm_dept as dept', DB::raw('case when fdp.is_active = true then \'Active\' else \'Non Active\' end as status'));
            $query->orderBy('nik');
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
        $data = DB::table('f_data_pegawai as dp')
            ->where('dp.nik_pegawai', $id)
            ->join('f_login_pegawai as lp', 'lp.id_pegawai', '=', 'dp.id_pegawai')
            ->leftjoin('f_department as d', 'd.id_dept', '=', DB::raw('ANY(lp.id_dept)'))
            ->leftjoin('f_sub_department as sd', 'sd.id_subdept', '=', DB::raw('ANY(lp.id_subdept)'))
            ->select('lp.id_pegawai as id', 'dp.nik_pegawai as nik', 'dp.nm_pegawai as nama', 'd.nm_dept as dept', 'sd.nm_subdept as subdept')
            ->get();

        $obj = new stdClass();
        $obj->id = $data[0]->id;
        $obj->nik = $data[0]->nik;
        $obj->nama = $data[0]->nama;
        $obj->dept = [];
        $obj->subdept = [];

        foreach ($data as $d) {
            if (!in_array($d->dept, $obj->dept, true))
                array_push($obj->dept, $d->dept);
            if (!in_array($d->subdept, $obj->subdept, true))
                array_push($obj->subdept, $d->subdept);
        }

        // $data = Karyawan::where('nik', $id)
        //     ->join('departemens', 'karyawans.id_departemen', '=', 'departemens.id_departemen')
        //     ->join('ruangs', 'karyawans.id_ruang', '=', 'ruangs.id_ruang')
        //     ->select('karyawans.nik', 'karyawans.nama', 'karyawans.id_departemen', 'karyawans.id_ruang', 'departemens.departemen', 'ruangs.ruang')
        //     ->first();

        return response()->json(["status" => "success", "data" => $obj], 200);
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
