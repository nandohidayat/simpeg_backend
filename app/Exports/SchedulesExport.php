<?php

namespace App\Exports;

use App\Schedule;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;

class SchedulesExport implements FromCollection
{

    protected $year, $month;

    function __construct($dept, $year, $month)
    {
        $this->dept = $dept;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $thisMonth = Carbon::create($this->year, $this->month);
        $firstday = $thisMonth->copy()->firstOfMonth();
        $lastday = $thisMonth->copy()->lastOfMonth();

        $dept = $this->dept;

        $query = DB::table('f_login_pegawai as lp')
            ->whereRaw('\'' . $dept . '\' = ANY(lp.id_dept)')
            ->join('f_data_pegawai as dp', 'dp.id_pegawai', '=', 'lp.id_pegawai')
            ->select('lp.id_pegawai', 'dp.nm_pegawai as nama');

        $jam = '';

        $date = $firstday->copy();

        while (!$date->greaterThan($lastday)) {
            $query->leftJoin('schedules as sch' . $date->day . '', function ($q) use ($date) {
                $q->on('sch' . $date->day . '.id_pegawai', '=', 'lp.id_pegawai');
                $q->where('sch' . $date->day . '.tgl', $date->toDateString());
            });
            $query->leftJoin('shifts as shf' . $date->day . '', 'sch' . $date->day . '.id_shift', '=', 'shf' . $date->day . '.id_shift');
            $query->addSelect('shf' . $date->day . '.kode as day' . $date->day . '');

            $jam .= 'COALESCE(
                CASE
                WHEN shf' . $date->day . '.selesai - shf' . $date->day . '.mulai > time \'00:00\' THEN
                    shf' . $date->day . '.selesai - shf' . $date->day . '.mulai
                ELSE
                    shf' . $date->day . '.selesai - shf' . $date->day . '.mulai + interval \'24 hours\'
                END
                , interval \'0 hours\')';

            if (!$date->equalTo($lastday)) $jam .= ' + ';

            $date->addDay();
        }

        $query->addSelect(DB::raw($jam .= 'as jam'));

        $schedules = $query->get();

        return $schedules;
    }
}
