<?php

namespace App\Imports;

use App\Schedule;
use App\ShiftDepartemen;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class SchedulesImport implements ToCollection
{
    protected $year, $month, $lastday;

    function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
        $this->lastday = Carbon::create($year, $month)->lastOfMonth()->day;
    }

    public function collection(Collection $rows)
    {
        $dept = $rows[0][0];

        $shifts = ShiftDepartemen::where([['id_dept', '=', $dept], ['status', '=', true]])
            ->join('shifts as s', 's.id_shift', '=', 'shift_departemens.id_shift')
            ->select('s.id_shift', 's.kode')
            ->get();

        $query = 'INSERT INTO schedules (dept, pegawai, tgl, shift) VALUES ';

        $i = 5;
        while (true) {
            if ($rows[$i][0] !== null) {
                $j = 3;
                while (true) {
                    $shift = $shifts->first(function ($item) use ($rows, $i, $j) {
                        return $item->kode == $rows[$i][$j];
                    });

                    $query .= '(\'' . $dept . '\', \'' . $rows[$i][0] . '\', \'' . Carbon::create($this->year, $this->month, $j - 2) . '\',' . (empty($rows[$i][$j] || $shift === null) ? 'null' :  $shift->id_shift) . ')';

                    if ($rows[4][$j] !== $this->lastday) {
                        $query .= ', ';
                    }

                    if ((int) $rows[4][$j] == $this->lastday) break;
                    $j++;
                }
            }

            if ($rows[$i + 2][1] === 'Keterangan') break;
            else if ($rows[$i][0] !== null) $query .= ', ';

            $i++;
        }

        $query .= ' ON CONFLICT ON CONSTRAINT schedules_ukey DO UPDATE SET shift = excluded.shift';

        DB::select($query);

        // error_log(json_encode($query));
    }
}
