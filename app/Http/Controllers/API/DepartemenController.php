<?php

namespace App\Http\Controllers\API;

use App\Departemen;
use App\Http\Controllers\Controller;
use App\ScheduleAccess;
use App\SIMDataPegawai;
use App\SIMDepartment;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartemenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = DB::table('f_department')->orderBy('nm_dept', 'asc');
        $data = null;

        $today = Carbon::now(new DateTimeZone('Asia/Jakarta'))->toDateString();

        if ((int) request()->select === 1) {
            if ((int) request()->schedule === 1) {
                $semua = DB::table('users as us')
                    ->join('akses_groups as ag', 'ag.id_group', '=', 'us.id_group')
                    ->where('us.id_pegawai', auth()->user()->id_pegawai)
                    ->where('ag.id_akses', 6)
                    ->where('ag.status', true)
                    ->first();

                if ($semua) {
                    $query = DB::table('f_department as fd');
                    if ((int) request()->ant === 1) {
                        $query->select('fd.id_dept as value', 'fd.nm_dept as label')->orderBy('label');
                    } else {
                        $query->select('fd.id_dept', 'fd.nm_dept')->orderBy('nm_dept');
                    }
                    $data = $query->get();
                } else {
                    $query = DB::table('log_departemens as ld')
                        ->where('ld.id_pegawai', auth()->user()->id_pegawai)
                        ->whereRaw('ld.masuk <= \'' . $today . '\'')
                        ->whereRaw('coalesce(ld.keluar, \'' . $today . '\') >= \'' . $today . '\'')
                        ->join('f_department as fd', 'fd.id_dept', '=', 'ld.id_dept');

                    if ((int) request()->ant === 1) {
                        $query->select('fd.id_dept as value', 'fd.nm_dept as label');
                    } else {
                        $query->select('fd.id_dept', 'fd.nm_dept');
                    }

                    $child = DB::table('log_departemens as ld')
                        ->where('ld.id_pegawai', auth()->user()->id_pegawai)
                        ->whereRaw('ld.masuk <= \'' . $today . '\'')
                        ->whereRaw('coalesce(ld.keluar, \'' . $today . '\') >= \'' . $today . '\'')
                        ->rightJoin('schedule_accesses as sa', 'sa.access', '=', 'ld.id_dept')
                        ->join('f_department as fd', 'fd.id_dept', '=', 'sa.dept');

                    if ((int) request()->ant === 1) {
                        $child->select('fd.id_dept as value', 'fd.nm_dept as label')->orderBy('label');
                    } else {
                        $child->select('fd.id_dept', 'fd.nm_dept')->orderBy('nm_dept');
                    }

                    $data = $query->union($child)->get();
                }
            } else {
                $query->select('nm_dept');
                $data = $query->pluck('nm_dept');
            }
        } else {
            $query->leftJoin('f_data_pegawai', 'f_data_pegawai.id_pegawai', '=', 'f_department.kepala_dept');
            $query->select('f_department.id_dept', 'nm_dept', 'nm_jabatan', 'nm_pegawai', 'nm_folder', 'id_pegawai');
            $data = $query->get();
        }

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
        $id_dept = DB::table('fi_id_dept_seq')->first()->id_dept;

        $dept = DB::table('f_department')
            ->insertGetId(
                [
                    'id_dept' => $id_dept,
                    'nm_dept' => $request->nama,
                    'nm_jabatan' => $request->jabatan,
                    'kepala_dept' => $request->id_pegawai,
                    'parent_code' => 0,
                    'is_active' => true
                ],
                'id_dept'
            );

        $data = DB::table('f_department')->where('f_department.id_dept', $dept)
            ->join('f_data_pegawai', 'f_data_pegawai.id_pegawai', '=', 'f_department.kepala_dept')
            ->select('f_department.id_dept', 'nm_dept', 'nm_jabatan', 'nm_pegawai', 'nm_folder', 'id_pegawai')
            ->first();

        return response()->json(["status" => "success", "data" => $data], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = SIMDataPegawai::whereRaw('' . $id . ' = ANY(id_dept)')->select('id_pegawai', 'nm_pegawai')->get();

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
        $dept = DB::table('f_department')->where('id_dept', $id)
            ->update(
                [
                    'nm_dept' => $request->nama,
                    'nm_jabatan' => $request->jabatan,
                    'nm_folder' => $request->folder
                ]
            );

        $data = DB::table('f_department')->where('f_department.id_dept', $id)
            ->join('f_data_pegawai', 'f_data_pegawai.id_pegawai', '=', 'f_department.kepala_dept')
            ->select('f_department.id_dept', 'nm_dept', 'nm_jabatan', 'nm_pegawai', 'nm_folder', 'id_pegawai')
            ->first();

        return response()->json(["status" => "success", "data" => $data], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = DB::table('f_department')->where('id_dept', $id)->delete();

        if ($data === 0) return response()->json(["status" => "error", "message" => "Data not found!"], 404);

        return response()->json(["status" => "success"], 201);
    }
}
