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

        $data = DB::table(DB::raw("generate_series('" . $firstday . "','" . $lastday . "', '1 day'::interval) tanggal"))
            ->leftJoin('log_departemens as ld', function ($join) use ($id) {
                $join->where('ld.id_pegawai', $id);
                $join->whereRaw('coalesce(ld.keluar, tanggal) >= tanggal');
                $join->on('ld.masuk', '<=', 'tanggal');
            })
            ->join('f_department as fd', 'fd.id_dept', '=', 'ld.id_dept')
            ->join('schedules as sch', function ($join) use ($id) {
                $join->where('sch.pegawai', $id);
                $join->on([['sch.dept', '=', 'ld.id_dept'], ['sch.tgl', '=', 'tanggal']]);
            })
            ->join('shifts as shf', 'shf.id_shift', '=', 'sch.shift')
            ->join('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'ld.id_pegawai')
            ->leftJoin('presensis as a', function ($join) {
                $join
                    ->on([
                        ['a.pin', '=', DB::raw('cast(dp.nik_pegawai as int)')],
                        ['a.datetime', '>=', DB::raw("(tanggal.tanggal + shf.mulai - interval '2 hours')")],
                        ['a.datetime', '<=', DB::raw("(case when shf.selesai > shf.mulai then tanggal.tanggal + shf.selesai else tanggal.tanggal + shf.selesai + interval '1 day' end)")]
                    ])
                    ->where([
                        ['a.status', '=', '0'],
                    ]);
            })
            ->leftJoin('presensis as b', function ($join) {
                $join
                    ->on([
                        ['b.pin', '=', 'a.pin'],
                        ['b.datetime', '>', DB::raw("(tanggal.tanggal + shf.mulai)")],
                        ['b.datetime', '<', DB::raw("(case when shf.selesai > shf.mulai then tanggal.tanggal + interval '1 day' else tanggal.tanggal + interval '2 days' end)")]
                    ])
                    ->where('b.status', '=', '1');
            })
            ->orderBy('tanggal')
            ->select(
                DB::raw('tanggal::date'),
                'fd.nm_dept as dept',
                'shf.kode as shift',
                DB::raw('cast(min(a.datetime) as time) as masuk'),
                DB::raw('cast(max(b.datetime) as time) as keluar'),
                DB::raw("(case when min(a.datetime) < (tanggal.tanggal + shf.mulai + interval '6 minutes') OR (cast(shf.mulai as time) = time '00:00') then 'Tidak Terlambat' else 'Terlambat' end) as keterangan"),
                DB::raw("(case when min(a.datetime) < (tanggal.tanggal + shf.mulai + interval '6 minutes') AND max(b.datetime) >= (tanggal.tanggal + shf.selesai) then (SELECT ph.pendapatan FROM pendapatan_harians as ph WHERE ph.tgl <= tanggal ORDER BY ph.tgl DESC LIMIT 1) else 0 end) as pendapatan")
            )
            ->groupBy('tanggal.tanggal', 'fd.nm_dept', 'shf.kode', 'shf.mulai', 'shf.selesai')
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
