<?php

namespace App\Http\Controllers\API;

use App\Departemen;
use App\Http\Controllers\Controller;
use App\SIMDataPegawai;
use App\SIMDepartment;
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
        if ((int)request()->select === 1) {
            $data = DB::connection('pgsql2')
                ->table('department')
                ->select('nm_dept')
                ->orderBy('nm_dept', 'asc')
                ->pluck('nm_dept');
        } else {
            $data = SIMDepartment::select('id_dept', 'nm_dept')
                ->orderBy('nm_dept')
                ->get();
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
