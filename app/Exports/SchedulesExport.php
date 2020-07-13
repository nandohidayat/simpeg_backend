<?php

namespace App\Exports;

use App\Schedule;
use App\ScheduleHoliday;
use App\ShiftDepartemen;
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
use stdClass;

class SchedulesExport implements FromCollection, WithHeadings, WithEvents
{

    protected $dept, $year, $month, $thisMonth, $firstday, $lastday, $count, $weekend;

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

    function emptySchedule()
    {
        $data = new Collection();
        $data->put('id_pegawai', null);
        $data->put('no', null);
        $data->put('nama', null);
        for ($i = 1; $i <= $this->lastday->day; $i++) {
            $data->put('shift' . $i . '', null);
        }

        return $data;
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

        $this->weekend = [];

        $date = $this->firstday->copy();

        while (!$date->greaterThan($this->lastday)) {
            $query->leftJoin('schedules as sch' . $date->day . '', function ($q) use ($date) {
                $q->where('sch' . $date->day . '.dept', '=', $this->dept);
                $q->on('sch' . $date->day . '.pegawai', '=', 'dp.id_pegawai');
                $q->where('sch' . $date->day . '.tgl', $date->toDateString());
            });

            $query->leftJoin('shifts as shf' . $date->day . '', 'shf' . $date->day . '.id_shift', '=', 'sch' . $date->day . '.shift');
            $query->addSelect('shf' . $date->day . '.kode as shift' . $date->day . '');

            if ($date->dayOfWeek === 0) array_push($this->weekend, $date->day);

            $date->addDay();
        }

        $schedule = $query->get();

        $order = explode(',', SIMDepartment::where('f_department.id_dept', $this->dept)
            ->leftJoin('schedule_orders as so', 'so.id_dept', '=', 'f_department.id_dept')
            ->select('so.order')
            ->first()
            ->order);

        if ($order[0] == "") {
            $order = array(0);
        }

        $max = max(array_map('intval', $order));
        $count = count($schedule);

        if ($max < $count - 1) {
            for ($i = $max + 1; $i < $count; $i++) {
                array_push($order, $i);
            }
        } else if ($max > $count) {
            while ($max !== $count) {
                unset($order[array_search($max, $order)]);
                $max = max(array_map('intval', $order));
            }
        }

        $this->count = count($order);
        $no = 0;
        $data = new Collection();

        foreach ($order as $o) {
            if ($o === "NaN") {
                $data->push($this->emptySchedule());
            } else {
                $schedule[(int) $o]->no = ++$no;
                $schedule[(int) $o]->nama = ucwords(strtolower($schedule[(int) $o]->nama));
                $data->push($schedule[(int) $o]);
            }
        }

        return $data;
    }

    public function headings(): array
    {
        $locale = $this->thisMonth->copy()->locale('id_ID');

        $dept = SIMDepartment::where('id_dept', $this->dept)->first()->nm_dept;

        $header = [
            [$this->dept, 'Jadwal ' . $dept . ''],
            [],
            [
                '' . $this->year . '/' . $this->month . '', 'Bulan : ' . $locale->isoFormat('MMMM Y') . ''
            ],
            [
                'Id',
                'No',
                'Nama',
                'Tanggal'
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
        $shift = ShiftDepartemen::where([['id_dept', '=', $this->dept], ['status', '=', true]])
            ->join('shifts as s', 's.id_shift', '=', 'shift_departemens.id_shift')
            ->select('s.kode', 's.mulai', 's.selesai', 's.keterangan')
            ->get();

        $holiday = ScheduleHoliday::whereBetween('tgl', [$this->firstday, $this->lastday])->select(DB::raw('EXTRACT(DAY FROM tgl) as tgl'))->pluck('tgl');

        return [
            AfterSheet::class => function (AfterSheet $event) use ($lastcol, $shift, $holiday) {
                $event->sheet->getDelegate()->getPageMargins()->setTop(0.764);
                $event->sheet->getDelegate()->getPageMargins()->setBottom(0.764);
                $event->sheet->getDelegate()->getPageMargins()->setLeft(0.256);
                $event->sheet->getDelegate()->getPageMargins()->setRight(0.256);
                $event->sheet->getDelegate()->getPageMargins()->setHeader(0.304);
                $event->sheet->getDelegate()->getPageMargins()->setFooter(0.304);

                $event->sheet->getDelegate()->getColumnDimension('A')->setVisible(false);

                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(4);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(21);
                for ($i = 4; $i <= $lastcol; $i++) {
                    $event->sheet->getDelegate()->getColumnDimension('' . $this->columnLetter($i) . '')->setWidth(3.7);
                }

                $event->sheet->getDelegate()->mergeCells('B1:' . $this->columnLetter($lastcol) . '1');
                $event->sheet->getDelegate()->mergeCells('B3:' . $this->columnLetter($lastcol) . '3');
                $event->sheet->getDelegate()->mergeCells('A4:A5');
                $event->sheet->getDelegate()->mergeCells('B4:B5');
                $event->sheet->getDelegate()->mergeCells('C4:C5');
                $event->sheet->getDelegate()->mergeCells('D4:' . $this->columnLetter($lastcol) . '4');

                $event->sheet->getDelegate()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('B4:' . $this->columnLetter($lastcol) . '5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('B4:' . $this->columnLetter($lastcol) . '5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $event->sheet->getDelegate()->getStyle('A1:' . $this->columnLetter($lastcol) . '5')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('B' . ($this->count + 7) . '')->getFont()->setBold(true);

                $event->sheet->getDelegate()->getStyle('B4:' . $this->columnLetter($lastcol) . '' . ($this->count + 5) . '')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $event->sheet->getDelegate()->getCell('B' . ($this->count + 7) . '')->setValue('Keterangan');
                foreach ($shift as $k => $s) {
                    $event->sheet->getDelegate()->getCell('B' . ($this->count + 8 + $k) . '')->setValue('' . $s->kode . '');
                    $event->sheet->getDelegate()->getCell('C' . ($this->count + 8 + $k) . '')->setValue('' . $s->keterangan . ' (' . Carbon::createFromTimeString($s->mulai)->format('H:i') . ' - ' . Carbon::createFromTimeString($s->selesai)->format('H:i') . ')');
                }

                $event->sheet->getDelegate()->getCell('F' . ($this->count + 8) . '')->setValue('Hari Minggu');
                $event->sheet->getDelegate()->getCell('F' . ($this->count + 9) . '')->setValue('Hari Libur');
                $event->sheet->getDelegate()->getStyle('J' . ($this->count + 8) . '')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffbababa');
                $event->sheet->getDelegate()->getStyle('J' . ($this->count + 9) . '')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff8b8b');

                foreach ($this->weekend as $w) {
                    $col = $this->columnLetter($w + 3);
                    $row = $this->count + 5;
                    $event->sheet->getDelegate()->getStyle('' . $col . '5:' . $col . '' . $row . '')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffbababa');
                }

                foreach ($holiday as $h) {
                    $col = $this->columnLetter((int) $h + 3);
                    $row = $this->count + 5;
                    $event->sheet->getDelegate()->getStyle('' . $col . '5:' . $col . '' . $row . '')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff8b8b');
                }

                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getStyle($event->sheet->getDelegate()->calculateWorksheetDimension())->getFont()->setName('Times New Roman');
            },
        ];
    }
}
