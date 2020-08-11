<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\LogDepartemen;
use App\SIMDataPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogDepartemenController extends Controller
{
    public function currentDept($pegawai)
    {
        $log = LogDepartemen::where('id_pegawai', $pegawai)->orderBy('tgl', 'asc')->get();
        $dept = [];

        foreach ($log as $l) {
            if ((int)$l->type === 0) {
                if (!in_array($l->id_dept, $dept)) array_push($dept, $l->id_dept);
            }
            if ((int)$l->type === 1) {
                $dept = array_diff($dept, $l->id_dept);
            }
        }

        return $dept;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = LogDepartemen::orderBy('tgl', 'desc')->get();

        if ($data === null) return response()->json(["status" => "failed"], 501);
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

        $log = new LogDepartemen;
        $log->id_pegawai = $input['pegawai'];
        $log->type = $input['type'];
        $log->id_dept = $input['dept'];
        $log->tgl = $input['tgl'];

        $log->save();

        DB::connection('pgsql2')
            ->table('data_pegawai')
            ->where('id_pegawai', $input['pegawai'])
            ->update(['id_dept' => DB::raw('ARRAY[\'' . implode("','", $this->currentDept($input['pegawai'])) . '\']')]);

        $data = LogDepartemen::where('id_log_departemen', $log->id_log_departemen)
            ->leftjoin('f_department', 'f_department.id_dept', '=', 'log_departemens.id_dept')
            ->select('log_departemens.id_log_departemen', 'log_departemens.tgl', 'log_departemens.type', DB::raw('(case when log_departemens.type = 0 then \'Masuk\' else \'Keluar\' end) as nm_type'), 'log_departemens.id_dept', 'f_department.nm_dept')
            ->orderBy('tgl', 'desc')
            ->get();

        return response()->json(["status" => "success", 'data' => $data], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = SIMDataPegawai::whereRaw('f_data_pegawai.nik_pegawai = \'' . sprintf("%05d", (int) $id) . '\'')
            ->rightJoin('log_departemens', 'log_departemens.id_pegawai', '=', 'f_data_pegawai.id_pegawai')
            ->leftjoin('f_department', 'f_department.id_dept', '=', 'log_departemens.id_dept')
            ->select('log_departemens.tgl', 'log_departemens.type', DB::raw('(case when log_departemens.type = 0 then \'Masuk\' else \'Keluar\' end) as nm_type'), 'log_departemens.id_dept', 'f_department.nm_dept')
            ->orderBy('tgl', 'desc')
            ->get();

        if ($data === null) return response()->json(["status" => "failed"], 501);
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
        $input = $request->all();

        $log = LogDepartemen::find($id);
        $log->id_pegawai = $input['pegawai'];
        $log->type = $input['type'];
        $log->id_dept = $input['dept'];
        $log->tgl = $input['tgl'];

        $log->save();

        DB::connection('pgsql2')
            ->table('data_pegawai')
            ->where('id_pegawai', $input['pegawai'])
            ->update(['id_dept' => DB::raw('ARRAY[\'' . implode("','", $this->currentDept($input['pegawai'])) . '\']')]);

        $data = LogDepartemen::where('id_log_departemen', $id)
            ->leftjoin('f_department', 'f_department.id_dept', '=', 'log_departemens.id_dept')
            ->select('log_departemens.id_log_departemen', 'log_departemens.tgl', 'log_departemens.type', DB::raw('(case when log_departemens.type = 0 then \'Masuk\' else \'Keluar\' end) as nm_type'), 'log_departemens.id_dept', 'f_department.nm_dept')
            ->orderBy('tgl', 'desc')
            ->get();

        return response()->json(["status" => "success", 'data' => $data], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $log = LogDepartemen::find($id);
        $log->delete();

        DB::connection('pgsql2')
            ->table('data_pegawai')
            ->where('id_pegawai', $log->id_pegawai)
            ->update(['id_dept' => DB::raw('ARRAY[\'' . implode("','", $this->currentDept($log->pegawai)) . '\']')]);

        return response()->json(["status" => "success"], 201);
    }
}
