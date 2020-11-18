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
                    $data = DB::table('f_department')->select('id_dept', 'nm_dept')->orderBy('nm_dept')->get();
                } else {
                    $query = DB::table('log_departemens as ld')
                        ->where('ld.id_pegawai', auth()->user()->id_pegawai)
                        ->whereRaw('ld.masuk <= \'' . $today . '\'')
                        ->whereRaw('coalesce(ld.keluar, \'' . $today . '\') >= \'' . $today . '\'')
                        ->join('f_department as fd', 'fd.id_dept', '=', 'ld.id_dept')
                        ->select('fd.id_dept', 'fd.nm_dept');

                    $child = DB::table('log_departemens as ld')
                        ->where('ld.id_pegawai', auth()->user()->id_pegawai)
                        ->whereRaw('ld.masuk <= \'' . $today . '\'')
                        ->whereRaw('coalesce(ld.keluar, \'' . $today . '\') >= \'' . $today . '\'')
                        ->rightJoin('schedule_accesses as sa', 'sa.access', '=', 'ld.id_dept')
                        ->join('f_department as fd', 'fd.id_dept', '=', 'sa.dept')
                        ->select('fd.id_dept', 'fd.nm_dept');

                    $data = $query->union($child)->orderBy('nm_dept')->get();
                }
            } else {
                $query->select('nm_dept');
                $data = $query->pluck('nm_dept');
            }
        } else {
            $query->select('id_dept', 'nm_dept');
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
        // $departemen = Departemen::where('id_bagian', '=', $request['id_bagian'])->orderBy('tingkat', 'desc')->first();

        // if ($departemen != null) {
        //     $tingkat = $departemen->tingkat;
        // } else {
        //     $tingkat = 0;
        // }
        // $request['tingkat'] = $tingkat + 1;

        $input = $request->all();
        $data = Departemen::create($input);

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
        $data = Departemen::updateOrCreate(['id_departemen' => $id], $request->all());

        if ($data === null) return response()->json(["status" => "failed"], 501);
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
        $data = Departemen::find($id);
        if ($data === null) return response()->json(["status" => "not found"], 404);

        $data->delete();
        return response()->json(["status" => "success"], 201);
    }
}
