<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PendapatanListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $first = Carbon::create(request()->year)->firstOfYear();
        $last = Carbon::create(request()->year)->lastOfYear();
        $query = DB::table('pendapatan_lists as pl')
            ->leftJoin('pendapatan_profils as pp', 'pl.id_pendapatan_profil', '=', 'pp.id_pendapatan_profil')
            ->where('pl.distribution', '>=', $first)
            ->where('pl.distribution', '<=', $last);

        if ((int)request()->select === 1) {
            $query->select('pl.id_pendapatan_list as value', DB::raw('concat_ws(\' \', to_char(pl.month, \'YYYY-MM\'), pp.title) as label'));
        } else {
            $query->select('pl.id_pendapatan_list', 'pl.id_pendapatan_profil', 'pp.title', 'pl.month', 'pl.distribution');
        }
        $data = $query->orderBy('distribution')
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
        $month = Carbon::createFromFormat('Y-m-d', $request->month . '-01');
        $distribution = Carbon::createFromFormat('Y-m-d', $request->distribution);

        DB::table('pendapatan_lists')
            ->insert([
                'id_pendapatan_profil' => $request->id_pendapatan_profil,
                'month' => $month,
                'distribution' => $distribution
            ]);

        return response()->json(["status" => "success"], 200);
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
        $month = Carbon::createFromFormat('Y-m-d', $request->month . '-01');
        $distribution = Carbon::createFromFormat('Y-m-d', $request->distribution);

        DB::table('pendapatan_lists')
            ->where('id_pendapatan_list', $request->id_pendapatan_list)
            ->update([
                'id_pendapatan_profil' => $request->id_pendapatan_profil,
                'month' => $month,
                'distribution' => $distribution
            ]);

        $data = DB::table('pendapatan_lists as pl')
            ->leftJoin('pendapatan_profils as pp', 'pl.id_pendapatan_profil', '=', 'pp.id_pendapatan_profil')
            ->where('pl.id_pendapatan_list', $request->id_pendapatan_list)
            ->select('pl.id_pendapatan_list', 'pl.id_pendapatan_profil', 'pp.title', 'pl.month', 'pl.distribution')
            ->first();

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('pendapatan_lists')
            ->where('id_pendapatan_list', $id)
            ->delete();

        return response()->json(["status" => "success"], 200);
    }
}
