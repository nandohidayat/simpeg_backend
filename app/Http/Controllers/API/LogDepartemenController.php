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
        $log = LogDepartemen::where('id_pegawai', $pegawai)->whereRaw('keluar IS NULL')->orderBy('masuk', 'asc')->get();
        $dept = [];

        error_log(json_encode($log));

        foreach ($log as $l) {
            if (!in_array($l->id_dept, $dept)) array_push($dept, $l->id_dept);
        }

        error_log(json_encode($dept));

        return $dept;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = LogDepartemen::orderBy('masuk', 'desc')->get();

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
        $log->id_dept = $input['dept'];
        $log->masuk = $input['masuk'];
        $log->keluar = isset($input['keluar']) ? $input['keluar'] : null;

        $log->save();

        DB::connection('pgsql2')
            ->table('data_pegawai')
            ->where('id_pegawai', $input['pegawai'])
            ->update(['id_dept' => DB::raw('ARRAY[\'' . implode("','", $this->currentDept($input['pegawai'])) . '\']')]);

        $data = LogDepartemen::where('id_log_departemen', $log->id_log_departemen)
            ->leftjoin('f_department', 'f_department.id_dept', '=', 'log_departemens.id_dept')
            ->select('log_departemens.id_log_departemen', 'log_departemens.id_dept', 'f_department.nm_dept', 'log_departemens.masuk', 'log_departemens.keluar')
            ->first();

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
            ->select('log_departemens.id_log_departemen', 'log_departemens.id_dept', 'f_department.nm_dept', 'log_departemens.masuk', 'log_departemens.keluar')
            ->orderBy('masuk', 'desc')
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
        $log->id_dept = $input['dept'];
        $log->masuk = $input['masuk'];
        $log->keluar = $input['keluar'];

        $log->save();

        DB::connection('pgsql2')
            ->table('data_pegawai')
            ->where('id_pegawai', $input['pegawai'])
            ->update(['id_dept' => DB::raw('ARRAY[\'' . implode("','", $this->currentDept($input['pegawai'])) . '\']')]);

        $data = LogDepartemen::where('id_log_departemen', $id)
            ->leftjoin('f_department', 'f_department.id_dept', '=', 'log_departemens.id_dept')
            ->select('log_departemens.id_log_departemen', 'log_departemens.id_dept', 'f_department.nm_dept', 'log_departemens.masuk', 'log_departemens.keluar')
            ->first();

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
            ->update(['id_dept' => DB::raw('ARRAY[\'' . implode("','", $this->currentDept($log->id_pegawai)) . '\']')]);

        return response()->json(["status" => "success"], 201);
    }
}
