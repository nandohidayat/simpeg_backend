<?php

namespace App\Exports;

use App\Schedule;
use App\ScheduleOrder;
use App\SIMDepartment;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class SchedulesExport implements FromCollection, WithHeadings, WithEvents
{

    protected $dept, $year, $month, $thisMonth, $firstday, $lastday;

    function columnLetter($c)
    {
        $c = intval($c);
        if ($c <= 0) return '';

        $letter = '';

        while ($c != 0) {
            $p = ($c - 1) % 26;
            $c = intval(($c - $p) / 26);
            $letter = chr(65 + $p) . $letter;
        }

        return $letter;
    }

    function __construct($dept, $year, $month)
    {
        $this->dept = $dept;
        $this->year = $year;
        $this->month = $month;
        $this->thisMonth = Carbon::create($year, $month);
        $this->firstday = $this->thisMonth->copy()->firstOfMonth();
        $this->lastday = $this->thisMonth->copy()->lastOfMonth();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = DB::table('f_data_pegawai as dp')
            ->whereRaw('\'' . $this->dept . '\' = ANY(dp.id_dept)')
            ->where('is_active', true)
            ->select('dp.id_pegawai', DB::raw('ROW_NUMBER () OVER (ORDER BY nik_pegawai) as no'), 'dp.nm_pegawai as nama')
            ->orderBy('nik_pegawai');

        $weekend = [];

        $date = $this->firstday->copy();

        while (!$date->greaterThan($this->lastday)) {
            $query->leftJoin('schedules as sch' . $date->day . '', function ($q) use ($date) {
                $q->where('sch' . $date->day . '.dept', '=', $this->dept);
                $q->on('sch' . $date->day . '.pegawai', '=', 'dp.id_pegawai');
                $q->where('sch' . $date->day . '.tgl', $date->toDateString());
            });

            $query->leftJoin('shifts as shf' . $date->day . '', 'shf' . $date->day . '.id_shift', '=', 'sch' . $date->day . '.shift');
            $query->addSelect('shf' . $date->day . '.kode as shift' . $date->day . '');

            if ($date->dayOfWeek === 0) array_push($weekend, $date->day);

            $date->addDay();
        }

        $schedule = $query->get();

        $order = explode(',', SIMDepartment::where('f_department.id_dept', $this->dept)
            ->leftJoin('schedule_orders as so', 'so.id_dept', '=', 'f_department.id_dept')
            ->select('so.order')
            ->first()
            ->order);

        return $schedule;
    }

    public function headings(): array
    {
        $locale = $this->thisMonth->copy()->locale('id_ID');

        $dept = SIMDepartment::where('id_dept', $this->dept)->first()->nm_dept;

        $header = [
            ['Jadwal ' . $dept . '', null, null],
            [],
            [
                'Bulan : ' . $locale->isoFormat('MMMM Y') . ''
            ],
            [
                'ID',
                'NO',
                'NAMA',
                'TANGGAL'
            ],
            [null, null, null]
        ];

        for ($i = $this->firstday->day; $i <= $this->lastday->day; $i++) {
            array_push($header[4], '' . $i . '');
        }

        return $header;
    }

    public function registerEvents(): array
    {
        $lastcol = 3 + $this->lastday->day;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($lastcol) {
                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getStyle($event->sheet->getDelegate()->calculateWorksheetDimension())->getFont()->setName('Times New Roman');

                $event->sheet->getDelegate()->getColumnDimension('A')->setVisible(false);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(4);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(25);

                $event->sheet->getDelegate()->mergeCells('A1:' . $this->columnLetter($lastcol) . '1');
                $event->sheet->getDelegate()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1')->getFont()->setBold(true);

                $event->sheet->getDelegate()->mergeCells('A3:' . $this->columnLetter($lastcol) . '3');
                $event->sheet->getDelegate()->getStyle('A3')->getFont()->setBold(true);

                $event->sheet->getDelegate()->mergeCells('A4:A5');
                $event->sheet->getDelegate()->mergeCells('B4:B5');
                $event->sheet->getDelegate()->mergeCells('C4:C5');

                $event->sheet->getDelegate()->mergeCells('D4:' . $this->columnLetter($lastcol) . '4');
                $event->sheet->getDelegate()->getStyle('D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $event->sheet->getDelegate()->getStyle('B4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('B4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->getDelegate()->getStyle('C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('C4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                for ($i = 4; $i <= $lastcol; $i++) {
                    $event->sheet->getDelegate()->getColumnDimension('' . $this->columnLetter($i) . '')->setWidth(3.2);
                    $event->sheet->getDelegate()->getStyle('' . $this->columnLetter($i) . '5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
