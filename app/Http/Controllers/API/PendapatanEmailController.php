<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class PendapatanEmailController extends Controller
{
    public function test()
    {
        $data = DB::table('pendapatan_pegawai')->where('id_pegawai', 'u-661')->first();
        $data->personalia = json_decode($data->personalia);
        $data->keuangan = json_decode($data->keuangan);


        return view('email/slip_gaji', ['data' => $data]);
        // return response()->json(['data' => $data]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->profil === 'undefined') {
            return response()->json(["status" => "error", 'message' => 'Profil nya dipilih dulu'], 501);
        }
        $query = DB::table('kirim_email as ke')
            ->leftJoin('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'ke.id_pegawai')
            ->select('ke.id_email', 'dp.nm_pegawai as nama', 'ke.penerima_email as email', 'ke.subjek_email as subjek', DB::raw('cast(ke.insertintodb as timestamp(0)) as kirim'), DB::raw('cast(ke.sendingdatetime as timestamp(0)) as terkirim'), 'ke.status_email as status');

        if (request()->karyawan && request()->karyawan !== 'undefined') {
            $query->where('ke.id_pegawai', request()->karyawan);
        }

        $data = $query->orderBy('terkirim', 'desc')->get();

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
