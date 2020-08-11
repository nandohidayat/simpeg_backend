<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Presensi;
use App\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AbsenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = null;

        if (request()->pegawai) {
            $date = Carbon::create(request()->year, request()->month);
            $firstday = $date->copy()->firstOfMonth();
            $current = Carbon::now();

            $lastday = $date->diffInMonths($current) === 0 ? $current : $date->copy()->lastOfMonth();

            $data = Schedule::where('schedules.pegawai', request()->pegawai)
                ->whereBetween('tgl', [$firstday, $lastday])
                ->whereNotNull('schedules.shift')
                ->leftjoin('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'schedules.pegawai')
                ->leftjoin('f_department as d', 'd.id_dept', '=', 'schedules.dept')
                ->leftJoin('shifts', 'schedules.shift', '=', 'shifts.id_shift')
                ->leftJoin('presensis as a', function ($join) {
                    $join
                        ->on([
                            ['a.pin', '=', DB::raw('cast(dp.nik_pegawai as int)')],
                            [DB::raw('cast(a.datetime as date)'), '=', DB::raw('cast(schedules.tgl as date)')],
                            [DB::raw('cast(a.datetime as time)'), '>', DB::raw("cast(shifts.mulai as time) - interval '2 hours'")],
                            [DB::raw('cast(a.datetime as time)'), '<', DB::raw("cast(shifts.selesai as time)")]
                        ])
                        ->where([
                            ['a.status', '=', '0'],
                        ]);
                })
                ->leftJoin('presensis as b', function ($join) {
                    $join
                        ->on([
                            ['b.pin', '=', 'a.pin'],
                            ['b.datetime', '>', 'a.datetime']
                        ])
                        ->where('b.status', '=', '1');
                })
                ->orderBy('schedules.tgl')
                ->select(
                    'schedules.id_schedule',
                    'schedules.tgl as tanggal',
                    'd.nm_dept as dept',
                    'shifts.kode as shift',
                    DB::raw('cast(min(a.datetime) as time) as masuk'),
                    DB::raw('cast(min(b.datetime) as time) as keluar'),
                    DB::raw("(case when cast(min(a.datetime) as time) < (cast(shifts.mulai as time) + interval '5 minutes 59 seconds') OR (cast(shifts.mulai as time) = time '00:00') then 'Tidak Terlambat' else 'Terlambat' end) as keterangan"),
                    DB::raw("(case when cast(min(a.datetime) as time) < (cast(shifts.mulai as time) + interval '5 minutes 59 seconds') then (SELECT ph.pendapatan FROM pendapatan_harians as ph WHERE ph.tgl <= schedules.tgl ORDER BY ph.tgl DESC LIMIT 1) else 0 end) as pendapatan")
                )
                ->groupBy('schedules.id_schedule', 'd.nm_dept', 'shifts.kode', 'shifts.mulai')
                ->get();

            $sum = 0;
            foreach ($data as $d) {
                $sum += $d->pendapatan;
            }

            return response()->json(["status" => "success", "data" => ["absen" => $data, "pendapatan" => $sum]], 200);
        }

        return response()->json(["status" => "success", "data" => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
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
        $date = Carbon::create(request()->year, request()->month);
        $firstday = $date->copy()->firstOfMonth();
        $current = Carbon::now();

        $lastday = $date->diffInMonths($current) === 0 ? $current : $date->copy()->lastOfMonth();

        $data = Schedule::where('schedules.dept', request()->dept)
            ->where('schedules.pegawai', $id)
            ->whereBetween('tgl', [$firstday, $lastday])
            ->whereNotNull('schedules.shift')
            ->leftjoin('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'schedules.pegawai')
            ->leftJoin('shifts', 'schedules.shift', '=', 'shifts.id_shift')
            ->leftJoin('presensis as a', function ($join) {
                $join
                    ->on([
                        ['a.pin', '=', DB::raw('cast(dp.nik_pegawai as int)')],
                        [DB::raw('cast(a.datetime as date)'), '=', DB::raw('cast(schedules.tgl as date)')],
                        [DB::raw('cast(a.datetime as time)'), '>', DB::raw("cast(shifts.mulai as time) - interval '2 hours'")],
                        [DB::raw('cast(a.datetime as time)'), '<', DB::raw("cast(shifts.selesai as time)")]
                    ])
                    ->where([
                        ['a.status', '=', '0'],
                    ]);
            })
            ->leftJoin('presensis as b', function ($join) {
                $join
                    ->on([
                        ['b.pin', '=', 'a.pin'],
                        ['b.datetime', '>', 'a.datetime']
                    ])
                    ->where('b.status', '=', '1');
            })
            ->orderBy('schedules.tgl')
            ->select(
                'schedules.id_schedule',
                'schedules.tgl as tanggal',
                'shifts.kode as shift',
                DB::raw('cast(min(a.datetime) as time) as masuk'),
                DB::raw('cast(min(b.datetime) as time) as keluar'),
                DB::raw("(case when cast(min(a.datetime) as time) < (cast(shifts.mulai as time) + interval '5 minutes') OR (cast(shifts.mulai as time) = time '00:00') then 'Tidak Terlambat' else 'Terlambat' end) as keterangan"),
                DB::raw("(case when cast(min(a.datetime) as time) < (cast(shifts.mulai as time) + interval '5 minutes') OR (cast(shifts.mulai as time) = time '00:00') then (SELECT ph.pendapatan FROM pendapatan_harians as ph WHERE ph.tgl <= schedules.tgl ORDER BY ph.tgl DESC LIMIT 1) else 0 end) as pendapatan")
            )
            ->groupBy('schedules.id_schedule', 'shifts.kode', 'shifts.mulai')
            ->get();

        $sum = 0;
        foreach ($data as $d) {
            $sum += $d->pendapatan;
        }

        return response()->json(["status" => "success", "data" => ["absen" => $data, "pendapatan" => $sum]], 200);
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
