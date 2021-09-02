<?php

namespace App\Exports;

use App\Absen;
use App\SIMDepartment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use stdClass;

class AbsenExport implements WithMultipleSheets
{
    use Exportable;

    protected $first, $last, $dept, $karyawan, $detail, $terlambat, $count;

    function __construct($month, $dept, $karyawan, $detail, $terlambat)
    {
        $date = Carbon::createFromFormat('Y-m-d', $month);
        $this->first = $date->copy()->firstOfMonth();
        $current = Carbon::now();
        $this->last = $date->diffInMonths($current) === 0 ? $current : $date->copy()->lastOfMonth();

        $this->dept = $dept;
        $this->karyawan = $karyawan;
        $this->detail = $detail === "true";
        $this->terlambat = $terlambat === "true";
    }

    public function sheets(): array
    {
        $query = DB::table('log_departemens as ld');

        if ($this->dept) {
            $query->where('ld.id_dept', $this->dept);
        }

        if ($this->karyawan) {
            $query->where('ld.id_pegawai', $this->karyawan);
        }

        $query->where('ld.masuk', '<=', $this->last)
            ->whereRaw('coalesce(ld.keluar, \'' . $this->last . '\') >= \'' . $this->first . '\'')
            ->join('f_data_pegawai as dp', function ($query) {
                $query->on('dp.id_pegawai', '=', 'ld.id_pegawai');
                $query->where('dp.is_active', true);
            })
            ->select('dp.nik_pegawai as nik',  'dp.nm_pegawai as nama')
            ->crossJoin(DB::raw("generate_series('" . $this->first . "','" . $this->last . "', '1 day'::interval) tanggal"))
            ->leftJoin('log_departemens as ld1', function ($join) {
                $join->on([['ld1.masuk', '<=', 'tanggal'], [DB::raw('coalesce(ld1.keluar, tanggal)'), '>=', 'tanggal'], ['ld1.id_pegawai', '=', 'dp.id_pegawai']]);
            })
            ->join('f_department as fd', 'fd.id_dept', '=', 'ld1.id_dept')
            ->join('schedules as sch', function ($join) {
                $join->on([['sch.dept', '=', 'ld1.id_dept'], ['sch.pegawai', '=', 'dp.id_pegawai'], ['sch.tgl', '=', 'tanggal']]);
            })
            ->join('shifts as shf', 'shf.id_shift', '=', 'sch.shift')
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
            ->orderBy('nik')
            ->orderBy('tanggal')
            ->addSelect(
                'fd.nm_dept as dept',
                DB::raw('tanggal::date as date'),
                DB::raw('(shf.kode || \' (\' || shf.mulai || \'-\' || shf.selesai || \')\') as shift'),
                DB::raw('cast(a.datetime as time) as masuk'),
                DB::raw('cast(b.datetime as time) as keluar'),
                DB::raw("(case when (cast(shf.mulai as time) = time '00:00') then 'Libur' when a.datetime < (tanggal.tanggal + shf.mulai + interval '6 minutes') then 'Tidak Terlambat' else 'Terlambat' end) as keterangan"),
            );

        $absen = $query->get();

        $array = [new AbsenSumsExport($absen, $this->first, $this->dept)];
        if ($this->detail) {
            array_push($array, new AbsenDetailsExport($absen, $this->first, $this->dept, $this->terlambat));
        }
        return $array;
    }
}
