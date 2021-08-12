<?php

namespace App\Exports;

use App\Absen;
use Maatwebsite\Excel\Concerns\FromCollection;

class AbsenExport implements FromCollection
{

    protected $month, $karyawan, $detail, $terlambat;

    function __construct($month, $karyawan, $detail, $terlambat)
    {
        $this->month = $month;
        $this->karyawan = $karyawan;
        $this->detail = $detail;
        $this->terlambat = $terlambat;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        return Absen::all();
    }
}
