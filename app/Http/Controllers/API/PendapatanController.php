<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\SIMDataPegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendapatanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $date = Carbon::createFromFormat('m-Y', request()->month)->addMonth()->lastOfMonth()->day();

        $tipe = request()->tipe;
        $profil = (int) request()->profil;

        $column = DB::table('pendapatan_profils')->select($tipe)->where('id_pendapatan_profil', $profil)->first();
        $column = json_decode($column->$tipe) ?? [];

        $query = DB::table('f_data_pegawai as fdp')
            ->leftJoin('log_departemens as ld', 'fdp.id_pegawai', '=', 'ld.id_pegawai')
            ->rightJoin('f_department as fd', 'ld.id_dept', '=', 'fd.id_dept')
            ->whereRaw('ld.masuk <= \'' . $date . '\'')
            ->whereRaw('coalesce(ld.keluar, \'' . $date . '\') >= \'' . $date . '\'')
            ->select('fdp.id_pegawai', 'fdp.nik_pegawai', 'fdp.nm_pegawai', DB::raw('json_agg(fd.nm_dept) as nm_dept'));

        $c_array = ['fdp.id_pegawai', 'fdp.nik_pegawai', 'fdp.nm_pegawai'];
        foreach ($column as $key => $value) {
            if ($value->hide) {
                continue;
            }

            $c = strtolower($key);
            $query->leftJoin('pendapatans as t_' . $c, function ($query) use ($c, $date) {
                $query->where('t_' . $c . '.label', $c);
                $query->where('t_' . $c . '.month', $date);
                $query->on('t_' . $c . '.id_pegawai', '=', 'fdp.id_pegawai');
            })
                ->addSelect('t_' . $c . '.value as ' . $c);

            array_push($c_array, $c);
        }

        $query->groupByRaw(implode(',', $c_array));
        $data = $query->orderBy('nik_pegawai')->get();

        $data = $data->map(function ($d) {
            $d->nm_dept = json_decode($d->nm_dept)[0] !== null ? json_decode($d->nm_dept) : [];
            return $d;
        });

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
        $date = Carbon::createFromFormat('m-Y', $request->month)->addMonth()->lastOfMonth()->day();
        $tipe = $request->tipe;
        $profil = (int) $request->profil;

        $column = DB::table('pendapatan_profils')->select($tipe)->where('id_pendapatan_profil', $profil)->first();
        $column = json_decode($column->$tipe) ?? [];

        $query = 'INSERT INTO pendapatans (month, id_pegawai ,label, value) VALUES ';

        foreach ($request->pendapatan as $p) {
            foreach ($column as $key => $value) {
                if ($value->hide) {
                    continue;
                }

                $c = strtolower($key);

                $query .= '(\'' . $date . '\',\'' . $p['id_pegawai'] . '\', \'' . $c . '\', ';
                if ($p[$c]) {
                    $query .= '\'' . $p[$c] . '\'), ';
                } else {
                    $query .= 'null), ';
                }
            }
        }

        $query = substr($query, 0, -2);
        $query .= ' ON CONFLICT ON CONSTRAINT pendapatans_ukey DO UPDATE SET value = excluded.value';

        DB::select($query);

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
        //
    }
}
