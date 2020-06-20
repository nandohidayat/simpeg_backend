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
    public function collection(Collection $rows)
    {
        $dept = $rows[0][0];

        $date = explode('/', $rows[2][0]);
        $year = $date[0];
        $month = $date[1];

        $shifts = ShiftDepartemen::where([['id_dept', '=', $dept], ['status', '=', true]])
            ->join('shifts as s', 's.id_shift', '=', 'shift_departemens.id_shift')
            ->select('s.id_shift', 's.kode')
            ->get();

        $query = 'INSERT INTO schedules (dept, pegawai, tgl, shift) VALUES ';

        $i = 5;
        while (true) {
            if ($rows[$i][0] === null) break;

            for ($j = 3; $j <= count($rows[5]) - 1; $j++) {
                $shift = $shifts->first(function ($item) use ($rows, $i, $j) {
                    return $item->kode == $rows[$i][$j];
                });

                $query .= '(\'' . $dept . '\', \'' . $rows[$i][0] . '\', \'' . Carbon::create($year, $month, $j - 2) . '\',' . (empty($rows[$i][$j]) ? 'null' :  $shift->id_shift) . ')';

                if ($j !== count($rows[5]) - 1) {
                    $query .= ', ';
                }
            }

            if ($rows[$i + 1][0] !== null) {
                $query .= ', ';
            }

            $i++;
        }

        $query .= ' ON CONFLICT ON CONSTRAINT schedules_ukey DO UPDATE SET shift = excluded.shift';

        DB::select($query);

        // error_log(json_encode($query));
    }
}
