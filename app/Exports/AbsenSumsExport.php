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

class AbsenSumsExport implements FromCollection, WithHeadings, WithEvents, WithTitle
{
    protected $absen, $first, $dept, $count;

    function __construct($absen, $first, $dept)
    {
        $this->absen = $absen;
        $this->first = $first;
        $this->dept = $dept;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $report = new Collection();
        $terlambat = 0;
        $total = 0;
        $temp = new stdClass();

        foreach ($this->absen as $k => $d) {
            if (isset($temp->nik) && $temp->nik !== $d->nik) {
                $temp->t = '' . $terlambat . '/' . $total;
                $temp->tt = '' . ($total - $terlambat) . '/' . $total;
                $temp->persentase = $total > 0 ? '' . number_format((($total - $terlambat) / $total) * 100, 2, ',', '') . '%' : "0,00%";
                $report->push($temp);
            }

            if (!isset($temp->nik) || $temp->nik !== $d->nik) {
                $temp = new stdClass();
                $temp->nik = $d->nik;
                $temp->nama = $d->nama;
                $temp->space = null;
                $terlambat = 0;
                $total = 0;
            }

            if ($d->keterangan === 'Terlambat') {
                $terlambat++;
            }

            if ($d->shift !== 'L (00:00:00-00:00:00)') {
                $total++;
            }
        }

        $temp->t = '' . $terlambat . '/' . $total;
        $temp->tt = '' . ($total - $terlambat) . '/' . $total;
        $temp->persentase = $total > 0 ? '' . number_format((($total - $terlambat) / $total) * 100, 2, ',', '') . '%' : "0,00%";

        $report->push($temp);

        $this->count = $report->count();

        return $report;
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
                null,
                'Terlambat',
                'T Terlambat',
                'Persentase',
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
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(18);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(15);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(15);

                $event->sheet->getDelegate()->mergeCells('A1:F1');
                $event->sheet->getDelegate()->mergeCells('A3:F3');
                for ($i = 4; $i <= $this->count + 4; $i++) {
                    $event->sheet->getDelegate()->mergeCells("B$i:C$i");
                }

                $event->sheet->getDelegate()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:F4')->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle('A4:F' . ($this->count + 4) . '')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $event->sheet->getDelegate()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $event->sheet->getDelegate()->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $event->sheet->getDelegate()->getStyle($event->sheet->getDelegate()->calculateWorksheetDimension())->getFont()->setName('Times New Roman');
            }
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
