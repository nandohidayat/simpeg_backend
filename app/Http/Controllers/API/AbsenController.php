<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
        $data = Bagian::updateOrCreate(['id_bagian' => $id], $request->all());

        if ($data === null) return response()->json(["status" => "failed"], 501);
        return response()->json(["status" => "success"], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $firstday = Carbon::create(request()->year, request()->month)->firstOfMonth();
        $lastday = Carbon::create(request()->year, request()->month)->lastOfMonth();

        $data = DB::connection('mysql2')
            ->table('log_presensi')
            ->whereBetween('DateTime', [$firstday, $lastday])
            ->where('PIN', $id)
            ->select(
                DB::raw('CAST(DateTime AS DATE) AS tanggal'),
                DB::raw('CAST(DateTime AS TIME) AS waktu'),
                DB::raw('IF(Status = 0, "Masuk", "Keluar") AS keterangan')
            )
            ->get();

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
