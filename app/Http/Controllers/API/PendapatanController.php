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
        $list = (int) request()->list;

        $data = DB::table('pendapatan_lists as pl')
            ->where('pl.id_pendapatan_list', $list)
            ->join('pendapatan_profils as pp', 'pp.id_pendapatan_profil', '=', 'pl.id_pendapatan_profil')
            ->select('pp.column', 'pl.month', 'pl.distribution as distri', 'pl.id_pendapatan_profil as profil', 'pl.locked', 'pl.edit')
            ->first();

        $edit = $data->edit;
        if ($data->edit) {
            $edit = DB::table('f_data_pegawai')->where('id_pegawai', $data->edit)->select('id_pegawai', 'nm_pegawai')->first();
        }

        $column = json_decode($data->column) ?? [];
        $date = Carbon::createFromFormat('Y-m-d', $data->month)->lastOfMonth();
        $last = Carbon::createFromFormat('Y-m-d', $data->distri);
        $first = Carbon::createFromFormat('Y-m-d', $data->distri)->firstOfMonth();
        $firstOfYear = Carbon::createFromFormat('Y-m-d', $data->distri)->firstOfYear();
        $profil = $data->profil;
        $locked = $data->locked;

        $query = DB::table('f_data_pegawai as fdp')
            ->leftJoin('log_departemens as ld', 'fdp.id_pegawai', '=', 'ld.id_pegawai')
            ->rightJoin('f_department as fd', 'ld.id_dept', '=', 'fd.id_dept')
            ->whereRaw('ld.masuk <= \'' . $date . '\'')
            ->whereRaw('coalesce(ld.keluar, \'' . $date . '\') >= \'' . $date . '\'')
            ->select('fdp.id_pegawai', 'fdp.nik_pegawai', 'fdp.nm_pegawai', DB::raw('json_agg(fd.nm_dept) as nm_dept'));

        $c_array = ['fdp.id_pegawai', 'fdp.nik_pegawai', 'fdp.nm_pegawai'];
        foreach ($column as $value) {
            if ($value->field === 'nik_pegawai' || $value->field === 'nm_pegawai' || $value->field === 'nm_dept') {
                continue;
            }

            $c = strtolower($value->field);
            array_push($c_array, $c);

            if (!$locked) {
                if ($c === 'pjk1') {
                    $query->leftJoin(DB::raw("(SELECT pen.label, pen.value, pl.distribution, pen.id_pegawai, row_number() over (order by pl.distribution desc) as rn from pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list) t_pjk1"), function ($query) use ($first, $firstOfYear) {
                        $query->where('t_pjk1.rn', 1);
                        $query->where('t_pjk1.label', 'pjk7');
                        $query->where('t_pjk1.distribution', '<', $first);
                        $query->where('t_pjk1.distribution', '>=', $firstOfYear);
                        $query->on('t_pjk1.id_pegawai', '=', 'fdp.id_pegawai');
                    });
                    $query->addSelect(DB::raw('cast(t_pjk1.value as DECIMAL) as pjk1'));
                    continue;
                }
                if ($c === 'pjk2') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhpremi' AND pl.distribution >= '$first' AND pl.distribution <= '$last') as pjk2"));
                    continue;
                }
                if ($c === 'pjk3') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhgaji' AND pl.distribution >= '$first' AND pl.distribution <= '$last') as pjk3"));
                    continue;
                }
                if ($c === 'pjk4') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhinsentiv' AND pl.distribution >= '$first' AND pl.distribution <= '$last') as pjk4"));
                    continue;
                }
                if ($c === 'pjk5') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhgajike' AND pl.distribution >= '$first' AND pl.distribution <= '$last') as pjk5"));
                    continue;
                }
                if ($c === 'pjk6') {
                    $query->addSelect(DB::raw("(SELECT (SUM(cast(pen.value AS DECIMAL)) * -1) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'premi3' AND pl.distribution >= '$first' AND pl.distribution <= '$last') as pjk6"));
                    continue;
                }

                if ($c === 'pjk13') {
                    $query->leftJoin(DB::raw("(SELECT pen.label, pen.value, pl.distribution, pen.id_pegawai, row_number() over (order by pl.distribution desc) as rn from pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list) t_pjk13"), function ($query) use ($first, $firstOfYear) {
                        $query->where('t_pjk13.rn', 1);
                        $query->where('t_pjk13.label', 'pjk12');
                        $query->where('t_pjk13.distribution', '<', $first);
                        $query->where('t_pjk13.distribution', '>=', $firstOfYear);
                        $query->on('t_pjk13.id_pegawai', '=', 'fdp.id_pegawai');
                    });
                    $query->addSelect(DB::raw('cast(t_pjk13.value as DECIMAL) as pjk13'));
                    continue;
                }

                if ($c === 'premi3') {
                }
            }
            $query->leftJoin('pendapatans as t_' . $c, function ($query) use ($c, $list) {
                $query->where('t_' . $c . '.id_pendapatan_list', $list);
                $query->where('t_' . $c . '.label', $c);
                $query->on('t_' . $c . '.id_pegawai', '=', 'fdp.id_pegawai');
            });
            if ($value->type === 'number') {
                $query->addSelect(DB::raw('cast(t_' . $c . '.value AS DECIMAL) as ' . $c));
            } else {
                $query->addSelect('t_' . $c . '.value as ' . $c);
            }
        }

        $query->groupByRaw(implode(',', $c_array));
        $data = $query->orderBy('nik_pegawai')->get();

        $data = $data->map(function ($d) {
            $d->nm_dept = json_decode($d->nm_dept)[0] !== null ? json_decode($d->nm_dept) : [];
            return $d;
        });

        if (!$locked) {
            foreach ($data as $key => $value) {
                $pen = $data[$key];
                $pen->pjk7 = $pen->pjk1 + $pen->pjk2 + $pen->pjk3 + $pen->pjk4 + $pen->pjk5 + $pen->pjk6;
                $pen->pjk8 = - ($pen->pjk3 * 0.05);
                $pen->pjk9 = - ($pen->pjk7 * 0.05);
                $pen->pjk10 = - ($pen->ptkp * $last->month);
                $pen->pjk11 = max($pen->pjk7 + $pen->pjk8 + $pen->pjk9 + $pen->pjk10, 0);
                $pen->pjk12 = $pen->pjk11 * 0.05;
                $pen->pjk14 = - ($pen->pjk12);
                $pen->pjk15 = $pen->pjk12 - $pen->pjk13;

                if (property_exists($pen, 'jmlhgaji')) {
                    $pen->pot1 = - ($pen->pjk15);
                    $pen->pot10 = $pen->pjk15;

                    $pen->jmlhpot = $pen->pot1
                        + $pen->pot2
                        + $pen->pot3
                        + $pen->pot4
                        + $pen->pot5
                        + $pen->pot6
                        + $pen->pot7
                        + $pen->pot8
                        + $pen->pot9
                        + $pen->pot10;

                    $pen->sebelumzakat = $pen->jmlhgaji + $pen->jmlhpot;
                    $pen->pot11 = - ($pen->sebelumzakat * 0.025);
                    $pen->diterima = $pen->sebelumzakat + $pen->pot11;
                    $pen->penyerahan = $pen->diterima + $pen->jmlhpotg;
                }

                $data[$key] = $pen;
            }
        }

        return response()->json(["status" => "success", "data" => ['pendapatan' => $data, 'profil' => $profil, 'date' => $last, 'edit' => $edit]], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
        $list = (int) $request->list;

        $data = DB::table('pendapatan_lists as pl')
            ->where('pl.id_pendapatan_list', $list)
            ->join('pendapatan_profils as pp', 'pp.id_pendapatan_profil', '=', 'pl.id_pendapatan_profil')
            ->select('pp.column', 'pl.month', 'pl.distribution as distri', 'pl.id_pendapatan_profil as profil', 'pl.locked')
            ->first();

        $column = json_decode($data->column) ?? [];

        $query = 'INSERT INTO pendapatans (id_pendapatan_list, id_pegawai ,label, value) VALUES ';

        foreach ($request->pendapatan as $p) {
            foreach ($column as $value) {
                if ($value->field === 'nik_pegawai' || $value->field === 'nm_pegawai' || $value->field === 'nm_dept') {
                    continue;
                }

                $c = strtolower($value->field);

                $query .= '(\'' . $list . '\',\'' . $p['id_pegawai'] . '\', \'' . $c . '\', ';
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
    }
}
