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
        $copy = (int) request()->copy;

        $data = DB::table('pendapatan_lists as pl')
            ->where('pl.id_pendapatan_list', $list)
            ->join('pendapatan_profils as pp', 'pp.id_pendapatan_profil', '=', 'pl.id_pendapatan_profil')
            ->select('pp.column', 'pl.month', 'pl.distribution as distri', 'pl.id_pendapatan_profil as profil', 'pl.locked')
            ->first();

        $column = json_decode($data->column) ?? [];
        $firstDay = Carbon::createFromFormat('Y-m-d', $data->month)->firstOfMonth();
        $lastDay = Carbon::createFromFormat('Y-m-d', $data->month)->lastOfMonth();
        $first = Carbon::createFromFormat('Y-m-d', $data->distri)->firstOfMonth();
        $last = Carbon::createFromFormat('Y-m-d', $data->distri);
        $firstOfYear = Carbon::createFromFormat('Y-m-d', $data->distri)->firstOfYear();
        $profil = $data->profil;
        $locked = $data->locked;

        $query = DB::table('f_data_pegawai as fdp')
            ->leftJoin('log_departemens as ld', 'fdp.id_pegawai', '=', 'ld.id_pegawai')
            ->join('f_department as fd', 'ld.id_dept', '=', 'fd.id_dept')
            ->whereRaw('coalesce(ld.keluar, ?) >= ?', [$lastDay, $firstDay])
            ->whereRaw('coalesce(ld.keluar, ?) <= ?', [$lastDay, $lastDay])
            ->select('fdp.id_pegawai', 'fdp.nik_pegawai', 'fdp.nm_pegawai', DB::raw('json_agg(fd.nm_dept) as nm_dept'));


        $c_array = ['fdp.id_pegawai', 'fdp.nik_pegawai', 'fdp.nm_pegawai'];
        foreach ($column as $value) {
            $c = strtolower($value->field);
            if (in_array($c, ['nik_pegawai', 'nm_pegawai', 'nm_dept'])) {
                continue;
            } else if (in_array($c, ['premi3v', 'premi4v1', 'premi4v2', 'premi4', 'premi6p', 'premi7p'])) {
                array_push($c_array, $c);
                continue;
            } else {
                array_push($c_array, $c);
            }

            if (!$locked) {
                if ($c === 'premi3') {
                    $absenRaw = DB::table('f_data_pegawai as fdp')
                        ->leftJoin('log_departemens as ld', 'fdp.id_pegawai', '=', 'ld.id_pegawai')
                        ->join('f_department as fd', 'ld.id_dept', '=', 'fd.id_dept')
                        ->whereRaw('coalesce(ld.keluar, ?) >= ?', [$lastDay, $firstDay])
                        ->whereRaw('coalesce(ld.keluar, ?) <= ?', [$lastDay, $lastDay])
                        ->crossJoin(DB::raw("generate_series('" . $firstDay . "','" . $lastDay . "', '1 day'::interval) tanggal"))
                        ->join('schedules as sch', function ($join) {
                            $join->on([['sch.dept', '=', 'ld.id_dept'], ['sch.tgl', '=', 'tanggal'], ['sch.pegawai', '=', 'fdp.id_pegawai']]);
                        })
                        ->join('shifts as shf', 'shf.id_shift', '=', 'sch.shift')
                        ->leftJoin('presensis as a', function ($join) {
                            $join
                                ->on([
                                    ['a.pin', '=', DB::raw('cast(fdp.nik_pegawai as int)')],
                                    ['a.datetime', '=', DB::raw("(SELECT MIN(a_t.datetime) FROM presensis as a_t WHERE a_t.datetime >= (tanggal.tanggal + shf.mulai - interval '2 hours') AND a_t.datetime <= (case when shf.selesai > shf.mulai then tanggal.tanggal + shf.selesai else tanggal.tanggal + shf.selesai + interval '1 day' end) AND a_t.pin = cast(fdp.nik_pegawai as int) AND a_t.status = 0)")]
                                ])
                                ->where([
                                    ['a.status', '=', '0'],
                                ]);
                        })
                        ->leftJoin('presensis as b', function ($join) {
                            $join
                                ->on([
                                    ['b.pin', '=', 'a.pin'],
                                    ['b.datetime', '=', DB::raw("(SELECT MAX(b_t.datetime) FROM presensis as b_t WHERE b_t.datetime >= (tanggal.tanggal + shf.mulai) AND b_t.datetime <= (case when shf.selesai > shf.mulai then tanggal.tanggal + interval '23 hours 59 minutes' else tanggal.tanggal + interval '1 day 23 hours 59 minutes' end) AND b_t.pin = cast(fdp.nik_pegawai as int) AND b_t.status = 1)")]
                                ])
                                ->where('b.status', '=', '1');
                        })
                        ->leftJoin('pendapatan_harians as ph', function ($join) {
                            $join->on('ph.tgl', '=', DB::raw('(SELECT MAX(ph_t.tgl) FROM pendapatan_harians as ph_t WHERE ph_t.tgl <= tanggal)'));
                        })
                        ->leftJoin('pendapatan_makans as pm', function ($join) {
                            $join->on('pm.tgl', '=', DB::raw('(SELECT MAX(pm_t.tgl) FROM pendapatan_makans as pm_t WHERE pm_t.tgl <= tanggal)'));
                        })
                        ->orderBy('tanggal')
                        ->select(
                            'fdp.id_pegawai',
                            DB::raw('tanggal::date'),
                            'shf.mulai as shift',
                            DB::raw("(case when (cast(shf.mulai as time) <> time '00:00') AND (a.datetime < (tanggal.tanggal + shf.mulai + interval '6 minutes') AND b.datetime >= (case when shf.selesai > shf.mulai then tanggal.tanggal + shf.selesai else tanggal.tanggal + shf.selesai + interval '1 day' end)) then ph.pendapatan else 0 end) as harian"),
                            DB::raw("(case when (cast(shf.mulai as time) <> time '00:00') AND (a.datetime IS NOT NULL OR b.datetime IS NOT NULL) then pm.pendapatan else 0 end) as makan")
                        );

                    $absen = DB::table(DB::raw("({$absenRaw->toSql()}) as absen"))
                        ->setBindings($absenRaw->getBindings())
                        ->select(
                            'id_pegawai',
                            DB::raw('SUM(CASE WHEN makan > 0 THEN 1 ELSE 0 END) as premi3v'),
                            DB::raw('SUM(makan) as premi3'),
                            DB::raw('SUM(CASE WHEN harian = 0 AND cast(shift as time) <> time \'00:00\' THEN 1 ELSE 0 END) as premi4v1'),
                            DB::raw('SUM(CASE WHEN harian > 0 THEN 1 ELSE 0 END) as premi4v2'),
                            DB::raw('SUM(harian) as premi4')
                        )
                        ->groupBy('id_pegawai');

                    $query->leftJoinSub($absen, 'ab', 'ab.id_pegawai', '=', 'fdp.id_pegawai');
                    $query->addSelect('premi3v', 'premi3', 'premi4v1', 'premi4v2', 'premi4');
                    continue;
                }

                if ($c === 'premi3p') {
                    $query->leftJoin('pendapatan_makans as t_premi3p', function ($join) use ($lastDay) {
                        $join->on('t_premi3p.tgl', '=', DB::raw('(SELECT MAX(pm_t.tgl) FROM pendapatan_makans as pm_t WHERE pm_t.tgl <= \'' . $lastDay . '\')'));
                    });
                    $query->addSelect('t_premi3p.pendapatan as premi3p', 't_premi3p.pendapatan as premi6p');
                    continue;
                }

                if ($c === 'premi4p') {
                    $query->leftJoin('pendapatan_harians as t_premi4p', function ($join) use ($lastDay) {
                        $join->on('t_premi4p.tgl', '=', DB::raw('(SELECT MAX(ph_t.tgl) FROM pendapatan_harians as ph_t WHERE ph_t.tgl <= \'' . $lastDay . '\')'));
                    });
                    $query->addSelect('t_premi4p.pendapatan as premi4p', 't_premi4p.pendapatan as premi7p');
                    continue;
                }

                if ($c === 'pjk1') {
                    $lastPjk7 = DB::table('pendapatans as pen')
                        ->leftJoin('pendapatan_lists as pl', 'pl.id_pendapatan_list', '=', 'pen.id_pendapatan_list')
                        ->select('pen.value', 'id_pegawai', DB::raw('row_number() over (partition by pen.id_pegawai order by pl.distribution desc) as rn'))
                        ->where('label', 'pjk7')
                        ->where('distribution', '<', $first)
                        ->where('distribution', '>=', $firstOfYear);

                    $query->leftJoinSub($lastPjk7, 't_pjk1', function ($query) {
                        $query->where('t_pjk1.rn', 1);
                        $query->on('t_pjk1.id_pegawai', '=', 'fdp.id_pegawai');
                    });

                    $query->addSelect(DB::raw('cast(t_pjk1.value as DECIMAL) as pjk1'));
                    continue;
                }
                if ($c === 'pjk2') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhpremi' AND pl.distribution >= '$first' AND pl.distribution < '$last' AND pen.id_pegawai = fdp.id_pegawai) as pjk2"));
                    continue;
                }
                if ($c === 'pjk3') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhgaji' AND pl.distribution >= '$first' AND pl.distribution < '$last' AND pen.id_pegawai = fdp.id_pegawai) as pjk3"));
                    continue;
                }
                if ($c === 'pjk4') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhinsentiv' AND pl.distribution >= '$first' AND pl.distribution < '$last' AND pen.id_pegawai = fdp.id_pegawai) as pjk4"));
                    continue;
                }
                if ($c === 'pjk5') {
                    $query->addSelect(DB::raw("(SELECT SUM(cast(pen.value AS DECIMAL)) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE pen.label = 'jmlhgajike' AND pl.distribution >= '$first' AND pl.distribution < '$last' AND pen.id_pegawai = fdp.id_pegawai) as pjk5"));
                    continue;
                }
                if ($c === 'pjk6') {
                    $query->addSelect(DB::raw("(SELECT (SUM(cast(pen.value AS DECIMAL)) * -1) FROM pendapatans as pen LEFT JOIN pendapatan_lists as pl ON pl.id_pendapatan_list = pen.id_pendapatan_list WHERE (pen.label = 'premi3' OR pen.label = 'premi6') AND pl.distribution > '$first' AND pl.distribution < '$last' AND pen.id_pegawai = fdp.id_pegawai) as pjk6"));
                    continue;
                }

                if ($c === 'pjk8') {
                    $lastPjk8 = DB::table('pendapatans as pen')
                        ->leftJoin('pendapatan_lists as pl', 'pl.id_pendapatan_list', '=', 'pen.id_pendapatan_list')
                        ->select('pen.value', 'id_pegawai', DB::raw('row_number() over (partition by pen.id_pegawai order by pl.distribution desc) as rn'))
                        ->where('label', 'pjk8')
                        ->where('distribution', '<', $last)
                        ->where('distribution', '>=', $firstOfYear);

                    $query->leftJoinSub($lastPjk8, 't_pjk8', function ($query) {
                        $query->where('t_pjk8.rn', 1);
                        $query->on('t_pjk8.id_pegawai', '=', 'fdp.id_pegawai');
                    });

                    $query->addSelect(DB::raw('cast(t_pjk8.value as DECIMAL) as pjk8'));
                    continue;
                }

                if ($c === 'pjk13') {
                    $lastPjk12 = DB::table('pendapatans as pen')
                        ->leftJoin('pendapatan_lists as pl', 'pl.id_pendapatan_list', '=', 'pen.id_pendapatan_list')
                        ->select('pen.value', 'id_pegawai', DB::raw('row_number() over (partition by pen.id_pegawai order by pl.distribution desc) as rn'))
                        ->where('label', 'pjk12')
                        ->where('distribution', '<', $first)
                        ->where('distribution', '>=', $firstOfYear);

                    $query->leftJoinSub($lastPjk12, 't_pjk13', function ($query) {
                        $query->where('t_pjk13.rn', 1);
                        $query->on('t_pjk13.id_pegawai', '=', 'fdp.id_pegawai');
                    });

                    $query->addSelect(DB::raw('cast(t_pjk13.value as DECIMAL) as pjk13'));
                    continue;
                }
            }

            $query->leftJoin('pendapatans as t_' . $c, function ($query) use ($c, $list, $copy) {
                $query->where('t_' . $c . '.id_pendapatan_list', $copy ? $copy : $list);
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

                if ((int)$profil === 5) {
                    $pen->premi6 = $pen->premi6v * $pen->premi6p;
                    $pen->premi7 = $pen->premi7v2 * $pen->premi7p;

                    $pen->jmlhpremi = $pen->premi1 + $pen->premi2 + $pen->premi3 + $pen->premi4 + $pen->premi6 + $pen->premi7;
                    $pen->premi5 = ($pen->jmlhpremi - ($pen->premi3 + $pen->premi6)) * (2.5 / 100);
                    $pen->diterima = $pen->jmlhpremi - $pen->premi5;

                    $pen->pjk2 = $pen->pjk2 + $pen->jmlhpremi;
                    $pen->pjk6 = $pen->pjk6 - ($pen->premi3 + $pen->premi6);

                    $pen->penyerahan = $pen->diterima + $pen->jmlhpotg;
                }

                if ((int)$profil === 1) {
                    $pen->pjk3 = $pen->pjk3 + $pen->jmlhgaji;
                    $pen->pjk8 = $pen->pjk8 - ($pen->pjk3 * 0.05);
                }

                $pen->pjk7 = $pen->pjk1 + $pen->pjk2 + $pen->pjk3 + $pen->pjk4 + $pen->pjk5 + $pen->pjk6;
                $pen->pjk9 = - ($pen->pjk7 * 0.05);
                $pen->pjk10 = - ($pen->ptkp * $last->month);
                $pen->pjk11 = max($pen->pjk7 + $pen->pjk8 + $pen->pjk9 + $pen->pjk10, 0);
                $pen->pjk12 = $pen->pjk11 * 0.05;
                $pen->pjk14 = - ($pen->pjk12);
                $pen->pjk15 = $pen->pjk12 - $pen->pjk13;

                if ((int)$profil === 1) {
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

        return response()->json(["status" => "success", "data" => ['pendapatan' => $data, 'profil' => $profil, 'date' => $last]], 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
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
        $list = (int) $id;
        $tipe = (int) $request->tipe;

        $data = DB::table('pendapatan_lists as pl')
            ->where('pl.id_pendapatan_list', $list)
            ->join('pendapatan_profils as pp', 'pp.id_pendapatan_profil', '=', 'pl.id_pendapatan_profil')
            ->select('pp.column')
            ->first();

        $column = json_decode($data->column) ?? [];

        $query = 'INSERT INTO pendapatans (id_pendapatan_list, id_pegawai ,label, value) VALUES ';

        foreach ($request->pendapatan as $p) {
            foreach ($column as $value) {
                if ((int)$value->author !== $tipe || $value->field === 'nik_pegawai' || $value->field === 'nm_pegawai' || $value->field === 'nm_dept') {
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
