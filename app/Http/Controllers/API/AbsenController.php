<?php

namespace App\Http\Controllers\API;

use App\Exports\AbsenExport;
use App\Http\Controllers\Controller;
use App\Presensi;
use App\Schedule;
use App\SIMDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
                        ['a.datetime', '=', DB::raw("(SELECT MIN(a_t.datetime) FROM presensis as a_t WHERE a_t.datetime >= (tanggal.tanggal + shf.mulai - interval '2 hours') AND a_t.datetime <= (case when shf.selesai > shf.mulai then tanggal.tanggal + shf.selesai else tanggal.tanggal + shf.selesai + interval '1 day' end) AND a_t.pin = cast(dp.nik_pegawai as int) AND a_t.status = 0)")]
                    ])
                    ->where([
                        ['a.status', '=', '0'],
                    ]);
            })
            ->leftJoin('presensis as b', function ($join) {
                $join
                    ->on([
                        ['b.pin', '=', 'a.pin'],
                        ['b.datetime', '=', DB::raw("(SELECT MAX(b_t.datetime) FROM presensis as b_t WHERE b_t.datetime >= (tanggal.tanggal + shf.mulai) AND b_t.datetime <= (case when shf.selesai > shf.mulai then tanggal.tanggal + interval '23 hours 59 minutes' else tanggal.tanggal + interval '1 day 23 hours 59 minutes' end) AND b_t.pin = cast(dp.nik_pegawai as int) AND b_t.status = 1)")]
                    ])
                    ->where('b.status', '=', '1');
            })
            ->leftJoin('pendapatan_harians as ph', function ($join) {
                $join->on('ph.tgl', '=', DB::raw('(SELECT MAX(ph_t.tgl) FROM pendapatan_harians as ph_t WHERE ph_t.tgl <= tanggal)'));
            })
            ->leftJoin('pendapatan_makans as pm', function ($join) {
                $join->on('pm.tgl', '=', DB::raw('(SELECT MAX(pm_t.tgl) FROM pendapatan_makans as pm_t WHERE pm_t.tgl <= tanggal)'));
            })
            ->select(
                DB::raw("DISTINCT ON (tanggal::date) tanggal::date"),
                DB::raw('fd.nm_dept as dept'),
                'shf.kode as shift',
                DB::raw('cast(a.datetime as time) as masuk'),
                DB::raw('cast(b.datetime as time) as keluar'),
                DB::raw("(case when (cast(shf.mulai as time) = time '00:00') then 'Libur' when a.datetime < (tanggal.tanggal + shf.mulai + interval '6 minutes') then 'Tidak Terlambat' else 'Terlambat' end) as keterangan"),
                DB::raw("(case when (cast(shf.mulai as time) <> time '00:00') AND (a.datetime < (tanggal.tanggal + shf.mulai + interval '6 minutes') AND b.datetime >= (case when shf.selesai > shf.mulai then tanggal.tanggal + shf.selesai else tanggal.tanggal + shf.selesai + interval '1 day' end)) then ph.pendapatan else 0 end) as harian"),
                DB::raw("(case when (cast(shf.mulai as time) <> time '00:00') AND (a.datetime IS NOT NULL OR b.datetime IS NOT NULL) then pm.pendapatan else 0 end) as makan")
            )
            ->groupBy('tanggal.tanggal', 'fd.nm_dept', 'shf.kode', 'shf.mulai', 'shf.selesai', 'shf.keterangan', 'a.datetime', 'b.datetime', 'ph.pendapatan', 'pm.pendapatan')
            ->orderBy('tanggal')
            ->orderBy(DB::raw("CASE WHEN keterangan = 'Tidak Terlambat' THEN 1 WHEN keterangan = 'Terlambat' THEN 2 WHEN keterangan = 'Libur' THEN 3 END"))
            ->get();

        $harian = 0;
        $makan = 0;
        foreach ($data as $d) {
            $harian += $d->harian;
            $makan += $d->makan;
        }

        return response()->json(["status" => "success", "data" => ["absen" => $data, "harian" => $harian, "makan" => $makan]], 200);
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

    public function export()
    {
        $dept = SIMDepartment::where('id_dept', request()->dept)->first();
        $month = Carbon::createFromFormat('Y-m-d', request()->month);
        return Excel::download(new AbsenExport(request()->month, request()->dept, request()->pegawai, request()->detail, request()->terlambat), 'Laporan Kehadiran ' . str_replace('/', ' ', $dept ? $dept->nm_dept : '') . '(' . $month->format('Y-m') . ').xlsx');
    }
}
