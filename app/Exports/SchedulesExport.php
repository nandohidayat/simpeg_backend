<?php

namespace App\Exports;

use App\Schedule;
use App\SIMDepartment;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SchedulesExport implements FromCollection, WithHeadings, ShouldAutoSize
{

    protected $dept, $year, $month;

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
        return Schedule::all();
    }

    public function headings(): array
    {
        $thisMonth = Carbon::create($this->year, $this->month);
        $firstday = $thisMonth->copy()->firstOfMonth();
        $lastday = $thisMonth->copy()->lastOfMonth();

        $locale = $thisMonth->copy()->locale('id_ID');

        $dept = SIMDepartment::where('id_dept', $this->dept)->first()->nm_dept;

        $header = [
            ['Jadwal ' . $dept . '', null, null],
            [],
            [
                'Bulan : ' . $locale->isoFormat('MMMM Y') . ''
            ],
            [
                'id',
                'nik',
                'nama',
            ]
        ];

        for ($i = $firstday->day; $i <= $lastday->day; $i++) {
            array_push($header[3], '' . $i . '');
        }

        return $header;
    }
}
