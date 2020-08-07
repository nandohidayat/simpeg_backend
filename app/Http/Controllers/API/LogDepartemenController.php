<?php

namespace App\Http\Controllers;

use App\LogDepartemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogDepartemenController extends Controller
{
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

        $log = LogDepartemen::where([
            ['id_pegawai', '=', $input['pegawai']],
            ['id_dept', '=', $input['dept']],
            ['tgl', '=', $input['tgl']]
        ])->first();

        if ($log) {
            return response()->json(["status" => "error", 'message' => 'Disposisi departemen yang sama tidak boleh memiliki tanggal yang sama'], 500);
        }

        $log = LogDepartemen::where([
            ['id_pegawai', '=', $input['pegawai']],
            ['id_dept', '=', $input['dept']],
            ['tgl', '>', $input['tgl']],
            ['type', '<>', $input['type']]
        ])->first();

        if (!$log) {
            $type = null;
            if ((int) $input['type'] === 0) {
                $type = 'ARRAY_APPEND';
            }
            if ((int) $input['type'] === 1) {
                $type = 'ARRAY_REMOVE';
            }

            DB::connection('pgsql2')
                ->select('UPDATE data_pegawai SET id_dept = ' . $type . '(id_dept, ' . $input['dept'] . ') WHERE id_pegawai = ' . $input['pegawai'] . '');
        }

        $log = new LogDepartemen;
        $log->id_pegawai = $input['pegawai'];
        $log->type = $input['type'];
        $log->id_dept = $input['dept'];
        $log->tgl = $input['tgl'];

        $log->save();

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
        $data = LogDepartemen::where('id_pegawai', $id)->orderBy('tgl', 'desc')->get();

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

        $log = LogDepartemen::where([
            ['id_pegawai', '=', $input['pegawai']],
            ['id_dept', '=', $input['dept']],
            ['tgl', '=', $input['tgl']]
        ])->first();

        if ($log) {
            return response()->json(["status" => "error", 'message' => 'Disposisi departemen yang sama tidak boleh memiliki tanggal yang sama'], 500);
        }

        $log = LogDepartemen::where([
            ['id_pegawai', '=', $input['pegawai']],
            ['id_dept', '=', $input['dept']],
            ['tgl', '>', $input['tgl']],
            ['type', '<>', $input['type']]
        ])->first();

        if (!$log) {
            $type = null;
            if ((int) $input['type'] === 0) {
                $type = 'ARRAY_APPEND';
            }
            if ((int) $input['type'] === 1) {
                $type = 'ARRAY_REMOVE';
            }

            DB::connection('pgsql2')
                ->select('UPDATE data_pegawai SET id_dept = ' . $type . '(id_dept, ' . $input['dept'] . ') WHERE id_pegawai = ' . $input['pegawai'] . '');
        }

        $log = LogDepartemen::find($id);
        $log->id_pegawai = $input['pegawai'];
        $log->type = $input['type'];
        $log->id_dept = $input['dept'];
        $log->tgl = $input['tgl'];

        $log->save();

        return response()->json(["status" => "success"], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $input = LogDepartemen::find($id);

        $log = LogDepartemen::where([
            ['id_pegawai', '=', $input->id_pegawai],
            ['id_dept', '=', $input->id_dept],
            ['tgl', '>', $input->tgl],
            ['type', '<>', $input->type]
        ])->first();

        if (!$log) {
            $type = null;
            if ((int) $input->type === 0) {
                $type = 'ARRAY_REMOVE';
            }
            if ((int) $input->type === 1) {
                $type = 'ARRAY_APPEND';
            }

            DB::connection('pgsql2')
                ->select('UPDATE data_pegawai SET id_dept = ' . $type . '(id_dept, ' . $input['dept'] . ') WHERE id_pegawai = ' . $input['pegawai'] . '');
        }

        $log = LogDepartemen::find($id);
        $log->delete();

        return response()->json(["status" => "success"], 201);
    }
}
