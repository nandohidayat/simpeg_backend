<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendapatanEmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        error_log(request()->profil);
        if (request()->profil == 'undefined') {
            error_log('lalala');
            return response()->json(["status" => "error", 'message' => 'Profil nya dipilih dulu'], 501);
        }
        $data = DB::table('pendapatan_pegawai as pp')
            ->where('pp.id_profilp', request()->profil)
            ->whereRaw("to_char(pp.bulan_kirim, 'YYYY-MM') = '" . request()->date . "'")
            ->leftJoin('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'pp.id_pegawai')
            ->rightJoin('kirim_email as ke', 'ke.id_pendapatan', '=', 'pp.id_pendapatan')
            ->select('ke.id_email', 'dp.nm_pegawai as nama', 'ke.penerima_email as email', 'ke.subjek_email as subjek', DB::raw('cast(ke.insertintodb as timestamp(0)) as kirim'), DB::raw('cast(ke.sendingdatetime as timestamp(0)) as terkirim'), 'ke.status_email as status')
            ->orderBy('terkirim', 'desc')
            ->get();

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
