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

    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $query = DB::table('karyawans');

        // if (request()->select == 1) {
        //     $ruang = Karyawan::where('nik', Auth::user()->nik)->first()->id_ruang;
        //     $query->where('id_ruang', $ruang);
        //     $query->select('nik', 'nama');
        //     $query->orderBy('nama', 'asc');
        // } else {
        //     $query->join('ruangs', 'karyawans.id_ruang', '=', 'ruangs.id_ruang');
        //     $query->join('departemens', 'karyawans.id_departemen', '=', 'departemens.id_departemen');
        //     $query->select('karyawans.nik', 'karyawans.nama', 'ruangs.ruang', 'departemens.departemen');
        //     $query->orderBy('nik', 'desc');
        // }

        // $data = $query->get();

        if (request()->select === '1') {
            $data = SIMLoginPegawai::whereRaw('\'' . request()->dept . '\' = ANY(f_login_pegawai.id_dept)')
                ->join('f_data_pegawai', 'f_data_pegawai.id_pegawai', '=', 'f_login_pegawai.id_pegawai')
                ->select('f_data_pegawai.id_pegawai', 'f_data_pegawai.nm_pegawai')
                ->get();
        }

        // $data = DB::connection('pgsql2')
        //     ->table('login_pegawai')
        //     ->leftJoin('data_pegawai', 'login_pegawai.id_pegawai', '=', 'data_pegawai.id_pegawai')
        //     ->leftJoin('department', function ($join) {
        //         $join->whereRaw('department.id_dept = ANY(login_pegawai.id_dept)');
        //     })
        //     ->select('data_pegawai.nik_pegawai as nik', 'data_pegawai.nm_pegawai as nama', 'data_pegawai.jenis_kelamin as sex', DB::raw('array_agg(department.nm_dept) AS dept'))
        //     ->groupBy('nik', 'nama', 'sex')
        //     ->get();

        // foreach ($data as $d) {
        //     $d->dept = explode('"', $d->dept);
        //     $temp = [];
        //     foreach ($d->dept as $key => $value) {
        //         if ($key % 2 != 0) array_push($temp, $value);
        //     }
        //     $d->dept = $temp;
        // }

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
