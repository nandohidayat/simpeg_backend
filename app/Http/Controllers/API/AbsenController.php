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
        $start = Carbon::now('+07:00')->startOfDay();
        $end = Carbon::now('+07:00')->endOfDay();

        $log = DB::connection('mysql2')->table('log_presensi')->whereBetween('DateTime', [$start, $end])->get();

        foreach ($log as $l) {
            if ($l->Status === 0) {
                $schedule = Schedule::where('schedules.nik', $l->PIN)
                    ->where('schedules.tgl', $start)
                    ->orderBy('schedules.tgl', 'desc')
                    ->select('schedules.masuk')
                    ->first();

                if ($schedule === null) continue;

                $masuk = Carbon::create($l->DateTime)->toTimeString();
                $mulai = Carbon::create($schedule->mulai)->toTimeString();

                if ($schedule->masuk === null) {
                    $schedule->masuk = $masuk;
                    $schedule->save();
                }
            } else if ($l->Status === 1) {
                $schedule = Schedule::where('schedules.nik', $l->PIN)
                    ->whereNotNull('schedules.masuk')
                    ->orderBy('schedules.tgl', 'desc')
                    ->select('schedules.keluar')
                    ->first();

                if ($schedule === null) continue;

                if ($schedule->keluar === null) {
                    $schedule->keluar = Carbon::create($l->DateTime)->toTimeString();
                }
            }
        }

        return response()->json(["status" => "success", "data" => $log], 200);
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

        $data = Schedule::where('schedules.pegawai', $id)
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
                DB::raw("(case when cast(a.datetime as time) < (cast(shifts.mulai as time) + interval '5 minutes') OR (cast(shifts.mulai as time) = time '00:00') then 'Tidak Terlambat' else 'Terlambat' end) as keterangan"),
                DB::raw("(case when cast(a.datetime as time) < (cast(shifts.mulai as time) + interval '5 minutes') OR (cast(shifts.mulai as time) = time '00:00') then (SELECT ph.pendapatan FROM pendapatan_harians as ph WHERE ph.tgl <= schedules.tgl ORDER BY ph.tgl DESC LIMIT 1) else 0 end) as pendapatan"),
            )
            ->groupBy('schedules.id_schedule', 'shifts.kode', 'keterangan', 'pendapatan')
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
