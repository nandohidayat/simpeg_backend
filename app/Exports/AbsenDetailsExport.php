<?php

namespace App\Exports;

use App\Absen;
use App\SIMDepartment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use stdClass;

class AbsenDetailsExport implements FromCollection, WithHeadings, WithEvents, WithTitle
{
    protected $absen, $first, $dept, $count;

    function __construct($absen, $first, $dept, $terlambat)
    {
        $this->absen = $absen;
        $this->first = $first;
        $this->dept = $dept;
        $this->terlambat = $terlambat;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        foreach ($this->absen as $k => $d) {
            if ($this->terlambat) {
                if ($d->keterangan !== 'Terlambat') {
                    unset($this->absen[$k]);
                    continue;
                }
            }
            $this->absen[$k]->date = Carbon::createFromFormat('Y-m-d', $this->absen[$k]->date)->locale('id_ID')->isoFormat('dddd, DD MMMM Y');
        }

        $this->count = $this->absen->count();

        return $this->absen;
    }

    public function headings(): array
    {
        $locale = $this->first->copy()->locale('id_ID');

        $dept = SIMDepartment::where('id_dept', $this->dept)->first();
        $dept = $dept ? $dept->nm_dept : "";

        $header = [
            ['Laporan Kehadiran ' . $dept . ''],
            [],
            [
                'Bulan : ' . $locale->isoFormat('MMMM Y') . ''
            ],
            [
                'NIK',
                'Nama',
                'Departemen',
                'Tanggal',
                'Shift',
                'Masuk',
                'Keluar',
                'Keterangan',
            ],
        ];

        return $header;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getPageMargins()->setTop(0.764);
                $event->sheet->getDelegate()->getPageMargins()->setBottom(0.764);
                $event->sheet->getDelegate()->getPageMargins()->setLeft(0.256);
                $event->sheet->getDelegate()->getPageMargins()->setRight(0.256);
                $event->sheet->getDelegate()->getPageMargins()->setHeader(0.304);
                $event->sheet->getDelegate()->getPageMargins()->setFooter(0.304);

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(9);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(26);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(26);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(24);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(21);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(10);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(16);

                $event->sheet->getDelegate()->mergeCells('A1:H1');
                $event->sheet->getDelegate()->mergeCells('A3:H3');

                $event->sheet->getDelegate()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $lastrow = $event->sheet->getDelegate()->getHighestRow();
                $event->sheet->getDelegate()->getStyle('D1:D' . $lastrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $event->sheet->getDelegate()->getStyle('E1:E' . $lastrow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $event->sheet->getDelegate()->getStyle('A1:H4')->getFont()->setBold(true);

                $event->sheet->getDelegate()->getStyle('A4:H' . ($this->count + 4) . '')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getStyle($event->sheet->getDelegate()->calculateWorksheetDimension())->getFont()->setName('Times New Roman');
            }
        ];
    }

    public function title(): string
    {
        return 'Details';
    }
}
